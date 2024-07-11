<?php
// File: approve_payments.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has the appropriate role (admin, assistant admin, or topline)
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin' && $_SESSION['role'] !== 'user')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id']) && isset($_POST['action'])) {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        // Start a transaction
        $conn->begin_transaction();

        try {
            // Fetch payment details
            $stmt = $conn->prepare("SELECT user_id, amount FROM manual_payments WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $payment_result = $stmt->get_result()->fetch_assoc();
            $user_id = $payment_result['user_id'];
            $amount = $payment_result['amount'];

            // Calculate friches amount (assuming 1 USD = 4 Friches)
            $friches_amount = $amount * 4;

            // Fetch the user's invited class
            $stmt = $conn->prepare("SELECT invited_class FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result()->fetch_assoc();
            $invited_class = $user_result['invited_class'];

            // Determine the new class based on the invited class and payment amount
            $new_class = $invited_class; // Default to invited class
            if ($amount >= 100 && $invited_class === 'B') {
                $new_class = 'B';
            } elseif ($amount >= 30 && ($invited_class === 'P' || $invited_class === 'B')) {
                $new_class = 'P';
            } elseif ($amount >= 10) {
                $new_class = 'E';
            }

            // Update payment status
            $stmt = $conn->prepare("UPDATE manual_payments SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();

            // Update user's class, payment status, and friches balance
            $stmt = $conn->prepare("UPDATE users SET class = ?, payment_status = 'paid', friches = friches + ? WHERE id = ?");
            $stmt->bind_param("sdi", $new_class, $friches_amount, $user_id);
            $stmt->execute();

            // Add a transaction record
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, friches_amount, transaction_type, status) VALUES (?, ?, ?, 'topup', 'completed')");
            $stmt->bind_param("idd", $user_id, $amount, $friches_amount);
            $stmt->execute();

            // Notify the user
            $notification_message = "Your payment has been approved. Your new class is {$new_class}-Class. {$friches_amount} Friches have been added to your account.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'payment_approved', '/dashboard.php')");
            $stmt->bind_param("is", $user_id, $notification_message);
            $stmt->execute();

            $conn->commit();
            $_SESSION['success_message'] = "Payment approved successfully. User upgraded to {$new_class}-Class and received {$friches_amount} Friches.";

            // Update session for the user if they're currently logged in
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
                $_SESSION['class'] = $new_class;
                $_SESSION['payment_status'] = 'paid';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error approving payment: " . $e->getMessage();
        }
    } elseif ($action === 'reject') {
        // Handle rejection
        $stmt = $conn->prepare("UPDATE manual_payments SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        // Notify the user
        $stmt = $conn->prepare("SELECT user_id FROM manual_payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_id = $result->fetch_assoc()['user_id'];

        $notification_message = "Your payment has been rejected. Please contact support for more information.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'payment_rejected', '/dashboard.php')");
        $stmt->bind_param("is", $user_id, $notification_message);
        $stmt->execute();

        $_SESSION['success_message'] = "Payment rejected successfully.";
    }

    // Redirect back to the page where payments are listed
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// Fetch pending payments
$stmt = $conn->prepare("
    SELECT mp.id, mp.user_id, u.username, u.invited_class, mp.amount, mp.created_at, mp.status
    FROM manual_payments mp
    JOIN users u ON mp.user_id = u.id
    WHERE mp.status = 'pending'
    ORDER BY mp.created_at DESC
");
$stmt->execute();
$pending_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Payments";
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1>Approve Payments</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_payments)): ?>
        <p>No pending payments to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Invited Class</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['username']); ?></td>
                        <td><?php echo htmlspecialchars($payment['invited_class']); ?></td>
                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include $root_path . '/includes/footer.php'; ?>