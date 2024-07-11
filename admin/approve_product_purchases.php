<?php
// File: admin/approve_product_purchases.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE product_payments SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Get payment details
            $stmt = $conn->prepare("SELECT user_id, product_id, amount FROM product_payments WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();

            // Grant access to the product
            $stmt = $conn->prepare("INSERT INTO user_products (user_id, product_id, purchase_date) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $payment['user_id'], $payment['product_id']);
            $stmt->execute();

            // Record the transaction
            $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'spend', ?)");
            $description = "Purchase of product ID: " . $payment['product_id'];
            $stmt->bind_param("ids", $payment['user_id'], $payment['amount'], $description);
            $stmt->execute();

            // Notify the user
            $notification_message = "Your product purchase has been approved.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'purchase_approved', '/products.php')");
            $stmt->bind_param("is", $payment['user_id'], $notification_message);
            $stmt->execute();

            $_SESSION['success_message'] = "Payment approved successfully.";
        } else {
            $_SESSION['error_message'] = "Error approving payment.";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE product_payments SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $payment_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Get user ID
            $stmt = $conn->prepare("SELECT user_id FROM product_payments WHERE id = ?");
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $payment = $stmt->get_result()->fetch_assoc();

            // Notify the user
            $notification_message = "Your product purchase has been rejected.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'purchase_rejected', '/products.php')");
            $stmt->bind_param("is", $payment['user_id'], $notification_message);
            $stmt->execute();

            $_SESSION['success_message'] = "Payment rejected successfully.";
        } else {
            $_SESSION['error_message'] = "Error rejecting payment.";
        }
    }

    header("Location: " . $base_path . "/admin/approve_product_purchases.php");
    exit();
}

// Fetch pending product purchases
$stmt = $conn->prepare("
    SELECT pp.id, pp.user_id, u.username, p.title as product_title, pp.amount, pp.file_path, pp.created_at
    FROM product_payments pp
    JOIN users u ON pp.user_id = u.id
    JOIN products p ON pp.product_id = p.id
    WHERE pp.status = 'pending'
    ORDER BY pp.created_at DESC
");
$stmt->execute();
$pending_purchases = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Product Purchases";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Approve Product Purchases</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_purchases)): ?>
        <p>No pending product purchases to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Bank Statement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_purchases as $purchase): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($purchase['username']); ?></td>
                        <td><?php echo htmlspecialchars($purchase['product_title']); ?></td>
                        <td>$<?php echo number_format($purchase['amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($purchase['created_at'])); ?></td>
                        <td>
                            <a href="<?php echo $base_path; ?>/uploads/product_payments/<?php echo $purchase['file_path']; ?>" target="_blank" class="btn btn-sm btn-info">View Statement</a>
                        </td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="payment_id" value="<?php echo $purchase['id']; ?>">
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