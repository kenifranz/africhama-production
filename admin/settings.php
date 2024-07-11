<?php
// File: admin/settings.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Check if the site_settings table exists, if not, create it
$table_check = $conn->query("SHOW TABLES LIKE 'site_settings'");
if ($table_check->num_rows == 0) {
    $create_table_sql = "CREATE TABLE site_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        site_name VARCHAR(255) NOT NULL,
        site_email VARCHAR(255) NOT NULL,
        maintenance_mode TINYINT(1) NOT NULL DEFAULT 0
    )";
    $conn->query($create_table_sql);

    // Insert default values
    $insert_default_sql = "INSERT INTO site_settings (site_name, site_email) VALUES ('Africhama', 'admin@africhama.com')";
    $conn->query($insert_default_sql);
}

$success_message = $error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_site_settings'])) {
        $site_name = $_POST['site_name'];
        $site_email = $_POST['site_email'];
        $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

        $stmt = $conn->prepare("UPDATE site_settings SET site_name = ?, site_email = ?, maintenance_mode = ?");
        $stmt->bind_param("ssi", $site_name, $site_email, $maintenance_mode);

        if ($stmt->execute()) {
            $success_message = "Site settings updated successfully.";
        } else {
            $error_message = "Error updating site settings.";
        }
    } elseif (isset($_POST['confirm_reset'])) {
        // Reset functionality
        $conn->begin_transaction();

        try {
            // Disable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");

            // Clear all transactions
            $conn->query("TRUNCATE TABLE transactions");

            // Clear all friches transactions
            $conn->query("TRUNCATE TABLE friches_transactions");

            // Clear all referrals
            $conn->query("TRUNCATE TABLE referrals");

            // Clear all notifications
            $conn->query("TRUNCATE TABLE notifications");

            // Clear all manual payments
            $conn->query("TRUNCATE TABLE manual_payments");

            // Clear all manual topups
            $conn->query("TRUNCATE TABLE manual_topups");

            // Clear all product payments
            $conn->query("TRUNCATE TABLE product_payments");

            // Clear all user products
            $conn->query("TRUNCATE TABLE user_products");

            // Delete all users except admin
            $conn->query("DELETE FROM users WHERE role != 'admin'");

            // Reset friches and account balance for admin users
            $conn->query("UPDATE users SET friches = 0, account_balance = 0 WHERE role = 'admin'");

            // Re-enable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");

            // Commit the transaction
            $conn->commit();

            $success_message = "System reset completed successfully.";
        } catch (Exception $e) {
            // An error occurred; rollback the transaction
            $conn->rollback();
            $error_message = "Error occurred during reset: " . $e->getMessage();
        } finally {
            // Ensure foreign key checks are re-enabled even if an error occurred
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}

// Fetch current settings
$stmt = $conn->prepare("SELECT * FROM site_settings LIMIT 1");
$stmt->execute();
$site_settings = $stmt->get_result()->fetch_assoc();

$page_title = "Admin Settings";
include $root_path . '/includes/admin_header.php';
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Admin Settings</h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Site Settings</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label for="site_name" class="form-label">Site Name</label>
                            <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_settings['site_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="site_email" class="form-label">Site Email</label>
                            <input type="email" class="form-control" id="site_email" name="site_email" value="<?php echo htmlspecialchars($site_settings['site_email']); ?>" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" <?php echo $site_settings['maintenance_mode'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                        </div>
                        <button type="submit" name="update_site_settings" class="btn btn-primary">Update Settings</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">Reset System</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3">Warning: This action will delete all non-admin users and clear all transactions. This action cannot be undone.</p>
                    <form method="post" onsubmit="return confirm('Are you absolutely sure you want to reset the system? This action cannot be undone.');">
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
    </div>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>