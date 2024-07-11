<?php
// File: admin/approve_payments.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin or assistant admin privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE manual_payments SET status = 'approved' WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE manual_payments SET status = 'rejected' WHERE id = ?");
    }

    $stmt->bind_param("i", $payment_id);
    $stmt->execute();

    // If approved, update user's payment status
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET payment_status = 'paid' WHERE id = (SELECT user_id FROM manual_payments WHERE id = ?)");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        // Notify the user that their payment was approved
        $stmt = $conn->prepare("SELECT user_id FROM manual_payments WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment_user = $result->fetch_assoc();
        if ($payment_user) {
            $notification_message = "Your payment has been approved.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'payment_approved', '/dashboard.php')");
            $stmt->bind_param("is", $payment_user['user_id'], $notification_message);
            $stmt->execute();
        }
    }

    $_SESSION['success_message'] = "Payment " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully.";
    header("Location: " . $base_path . "/admin/approve_payments.php");
    exit();
}

// Fetch all pending payments
$stmt = $conn->prepare("
    SELECT mp.id, mp.user_id, u.username, mp.amount, mp.file_path, mp.created_at, mp.topline_id
    FROM manual_payments mp
    JOIN users u ON mp.user_id = u.id
    WHERE mp.status = 'pending'
    ORDER BY mp.created_at DESC
");
$stmt->execute();
$pending_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Payments";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Approve Payments</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_payments)): ?>
        <p>No pending payments to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Bank Statement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['username']); ?></td>
                        <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                        <td><a href="<?php echo $base_path; ?>/uploads/bank_statements/<?php echo $payment['file_path']; ?>" target="_blank" class="btn btn-sm btn-info">View Statement</a></td>
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

<?php include $root_path . '/includes/admin_footer.php'; ?>