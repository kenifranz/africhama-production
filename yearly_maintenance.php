<?php
// File: yearly_maintenance.php
session_start();
require_once 'db_connect.php';
require_once 'points_system.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pointsSystem = new PointsSystem($conn);

$stmt = $conn->prepare("SELECT friches FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_friches = $stmt->get_result()->fetch_assoc()['friches'];

$stmt = $conn->prepare("
    SELECT MAX(created_at) as last_payment
    FROM friches_transactions
    WHERE user_id = ? AND transaction_type = 'spend' AND description = 'Yearly maintenance fee'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last_payment = $stmt->get_result()->fetch_assoc()['last_payment'];

$maintenance_due = true;
$days_until_due = 0;

if ($last_payment) {
    $last_payment_date = new DateTime($last_payment);
    $next_due_date = $last_payment_date->modify('+1 year');
    $today = new DateTime();
    
    if ($today < $next_due_date) {
        $maintenance_due = false;
        $days_until_due = $today->diff($next_due_date)->days;
    }
}

$maintenance_fee_friches = 80; // 80 Friches for yearly maintenance

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    
    if ($payment_method === 'points') {
        if ($pointsSystem->usePointsForYearlyMaintenance($user_id)) {
            $success_message = "Yearly maintenance fee paid successfully using points.";
            $maintenance_due = false;
            $days_until_due = 365;
        } else {
            $error_message = "Insufficient points to pay the maintenance fee.";
        }
    } elseif ($payment_method === 'friches') {
        if ($user_friches >= $maintenance_fee_friches) {
            $stmt = $conn->prepare("UPDATE users SET friches = friches - ? WHERE id = ?");
            $stmt->bind_param("di", $maintenance_fee_friches, $user_id);
            if ($stmt->execute()) {
                $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'spend', 'Yearly maintenance fee')");
                $stmt->bind_param("id", $user_id, $maintenance_fee_friches);
                $stmt->execute();
                $success_message = "Yearly maintenance fee paid successfully using Friches.";
                $maintenance_due = false;
                $days_until_due = 365;
            } else {
                $error_message = "Payment failed. Please try again later.";
            }
        } else {
            $error_message = "Insufficient Friches to pay the maintenance fee.";
        }
    }
}

$user_points = $pointsSystem->getUserPoints($user_id);

$page_title = "Yearly Maintenance";
include './includes/header.php';
?>

<h1>Yearly Account Maintenance</h1>

<?php if (isset($success_message)): ?>
    <div class="success-message"><?php echo $success_message; ?></div>
<?php elseif (isset($error_message)): ?>
    <div class="error-message"><?php echo $error_message; ?></div>
<?php else: ?>
    <?php if ($maintenance_due): ?>
        <p>Your yearly maintenance fee of <?php echo $maintenance_fee_friches; ?> Friches is due.</p>
        <p>You currently have <?php echo $user_points; ?> points and <?php echo $user_friches; ?> Friches.</p>
        <form action="yearly_maintenance.php" method="post">
            <h2>Payment Method:</h2>
            <div class="payment-option">
                <input type="radio" id="pay-points" name="payment_method" value="points" <?php echo $user_points >= 20 ? '' : 'disabled'; ?>>
                <label for="pay-points">Pay with Points (20 points required)</label>
            </div>
            <div class="payment-option">
                <input type="radio" id="pay-friches" name="payment_method" value="friches" <?php echo $user_friches >= $maintenance_fee_friches ? '' : 'disabled'; ?>>
                <label for="pay-friches">Pay with Friches (<?php echo $maintenance_fee_friches; ?> Friches)</label>
            </div>
            <button type="submit" class="button">Pay Maintenance Fee</button>
        </form>
    <?php else: ?>
        <p>Your account is up to date. Next maintenance payment due in <?php echo $days_until_due; ?> days.</p>
    <?php endif; ?>
<?php endif; ?>

<?php include './includes/footer.php'; ?>