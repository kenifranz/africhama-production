<?php
// File: upgrade.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT class, friches FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$upgrade_options = [];
switch ($user['class']) {
    case 'E':
        $upgrade_options['P'] = ['friches' => 120, 'dollars' => 30];
        $upgrade_options['B'] = ['friches' => 400, 'dollars' => 100];
        break;
    case 'P':
        $upgrade_options['B'] = ['friches' => 400, 'dollars' => 100];
        break;
    case 'B':
        // No upgrade options for Class B
        break;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_class = $_POST['new_class'];
    
    if (!isset($upgrade_options[$new_class])) {
        $_SESSION['error_message'] = "Invalid upgrade option selected.";
        header("Location: " . $base_path . "/upgrade.php");
        exit();
    }
    
    $upgrade_cost = $upgrade_options[$new_class]['friches'];

    if ($user['friches'] >= $upgrade_cost) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Deduct friches from user
            $stmt = $conn->prepare("UPDATE users SET class = ?, friches = friches - ? WHERE id = ?");
            $stmt->bind_param("sdi", $new_class, $upgrade_cost, $user_id);
            $stmt->execute();

            // Record the transaction
            $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'spend', ?)");
            $description = "Upgrade to " . $new_class . "-Class";
            $stmt->bind_param("ids", $user_id, $upgrade_cost, $description);
            $stmt->execute();

            $conn->commit();
            $_SESSION['success_message'] = "Congratulations! You've successfully upgraded to {$new_class}-Class.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Upgrade failed. Please try again later.";
        }
    } else {
        $_SESSION['error_message'] = "Insufficient Friches. You need {$upgrade_cost} Friches to upgrade.";
    }

    header("Location: " . $base_path . "/dashboard.php");
    exit();
}

$page_title = "Upgrade Membership";
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1>Upgrade Your Membership</h1>
    <p>Your current class: <?php echo $user['class']; ?>-Class</p>
    <p>Your Friches balance: <?php echo number_format($user['friches'], 2); ?> Friches</p>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (!empty($upgrade_options)): ?>
        <form action="upgrade.php" method="post">
            <h2>Available Upgrades:</h2>
            <?php foreach ($upgrade_options as $class => $cost): ?>
                <div class="form-check mb-3">
                    <input type="radio" id="upgrade-<?php echo $class; ?>" name="new_class" value="<?php echo $class; ?>" class="form-check-input" required>
                    <label for="upgrade-<?php echo $class; ?>" class="form-check-label">
                        Upgrade to <?php echo $class; ?>-Class (Cost: <?php echo $cost['friches']; ?> Friches)
                    </label>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Upgrade Now</button>
        </form>
    <?php else: ?>
        <p>You've reached the highest membership class. No further upgrades are available.</p>
    <?php endif; ?>
</div>

<?php include $root_path . '/includes/footer.php'; ?>