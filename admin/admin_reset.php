<?php
// File: admin/admin_reset.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");

        // Clear all transactions
        $stmt = $conn->prepare("TRUNCATE TABLE transactions");
        $stmt->execute();

        // Clear all friches transactions
        $stmt = $conn->prepare("TRUNCATE TABLE friches_transactions");
        $stmt->execute();

        // Clear all referrals
        $stmt = $conn->prepare("TRUNCATE TABLE referrals");
        $stmt->execute();

        // Clear all notifications
        $stmt = $conn->prepare("TRUNCATE TABLE notifications");
        $stmt->execute();

        // Clear all manual payments
        $stmt = $conn->prepare("TRUNCATE TABLE manual_payments");
        $stmt->execute();

        // Clear all manual topups
        $stmt = $conn->prepare("TRUNCATE TABLE manual_topups");
        $stmt->execute();

        // Clear all product payments
        $stmt = $conn->prepare("TRUNCATE TABLE product_payments");
        $stmt->execute();

        // Clear all user products
        $stmt = $conn->prepare("TRUNCATE TABLE user_products");
        $stmt->execute();

        // Delete all users except admin
        $stmt = $conn->prepare("DELETE FROM users WHERE role != 'admin'");
        $stmt->execute();

        // Reset friches and account balance for admin users
        $stmt = $conn->prepare("UPDATE users SET friches = 0, account_balance = 0 WHERE role = 'admin'");
        $stmt->execute();

        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");

        // Commit the transaction
        $conn->commit();

        $message = "Reset completed successfully. All non-admin users and transactions have been cleared.";
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        $message = "Error occurred during reset: " . $e->getMessage();
    } finally {
        // Ensure foreign key checks are re-enabled even if an error occurred
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}

$page_title = "Admin Reset";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Admin Reset</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Reset System</h5>
            <p class="card-text">Warning: This action will delete all non-admin users and clear all transactions. This action cannot be undone.</p>
            <form action="admin_reset.php" method="post" onsubmit="return confirm('Are you absolutely sure you want to reset the system? This action cannot be undone.');">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="confirm_reset" name="confirm_reset" required>
                    <label class="form-check-label" for="confirm_reset">
                        I understand that this action will delete all non-admin users and clear all transactions.
                    </label>
                </div>
                <button type="submit" class="btn btn-danger">Reset System</button>
            </form>
        </div>
    </div>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>