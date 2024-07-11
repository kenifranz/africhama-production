<?php
// File: admin/approve_downline_payments.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user has any downlines
$stmt = $conn->prepare("SELECT COUNT(*) as downline_count FROM referrals WHERE referrer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$has_downlines = ($result->fetch_assoc()['downline_count'] > 0);

if (!$has_downlines) {
    header("Location: " . $base_path . "/admin/index.php");
    exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE manual_payments SET status = 'approved' WHERE id = ? AND topline_id = ?");
        $stmt->bind_param("ii", $payment_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Update user's payment status
            $stmt = $conn->prepare("UPDATE users SET payment_status = 'paid' WHERE id = (SELECT user_id FROM manual_payments WHERE id = ?)");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();

            // Notify the downline
            $stmt = $conn->prepare("SELECT user_id FROM manual_payments WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $downline_id = $result->fetch_assoc()['user_id'];

            $notification_message = "Your payment has been approved by your topline.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'payment_approved', '/dashboard.php')");
            $stmt->bind_param("is", $downline_id, $notification_message);
            $stmt->execute();

            $_SESSION['success_message'] = "Payment approved successfully.";
        } else {
            $_SESSION['error_message'] = "Error approving payment.";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE manual_payments SET status = 'rejected' WHERE id = ? AND topline_id = ?");
        $stmt->bind_param("ii", $payment_id, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Notify the downline
            $stmt = $conn->prepare("SELECT user_id FROM manual_payments WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $downline_id = $result->fetch_assoc()['user_id'];

            $notification_message = "Your payment has been rejected by your topline.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'payment_rejected', '/dashboard.php')");
            $stmt->bind_param("is", $downline_id, $notification_message);
            $stmt->execute();

            $_SESSION['success_message'] = "Payment rejected successfully.";
        } else {
            $_SESSION['error_message'] = "Error rejecting payment.";
        }
    }

    header("Location: " . $base_path . "/admin/approve_downline_payments.php");
    exit();
}

// Fetch pending payments from downlines
$stmt = $conn->prepare("
    SELECT mp.id, mp.user_id, u.username, mp.amount, mp.file_path, mp.created_at
    FROM manual_payments mp
    JOIN users u ON mp.user_id = u.id
    WHERE mp.topline_id = ? AND mp.status = 'pending'
    ORDER BY mp.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Downline Payments";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Approve Downline Payments</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_payments)): ?>
        <p>No pending payments from downlines to approve.</p>
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
                        <td>
                            <a href="<?php echo $base_path; ?>/uploads/bank_statements/<?php echo $payment['file_path']; ?>" target="_blank" class="btn btn-sm btn-info">View Statement</a>
                        </td>
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

<script>
    // You can add any additional JavaScript here if needed
</script>

<?php include $root_path . '/includes/admin_footer.php'; ?>