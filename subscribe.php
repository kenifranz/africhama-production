<?php
// File: subscribe.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check current subscription status
$stmt = $conn->prepare("
    SELECT id, end_date 
    FROM subscriptions 
    WHERE user_id = ? AND status = 'active' AND end_date >= CURDATE()
    ORDER BY end_date DESC 
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current_subscription = $stmt->get_result()->fetch_assoc();

$subscription_price = 15; // USD per year

$page_title = "Subscribe";
include './includes/header.php';
?>

<h1>Africhama Subscription</h1>

<?php if ($current_subscription): ?>
    <p>You have an active subscription until <?php echo $current_subscription['end_date']; ?>.</p>
    <p>Would you like to renew your subscription?</p>
<?php else: ?>
    <p>Subscribe now to access premium content!</p>
<?php endif; ?>

<p>Subscription Price: $<?php echo $subscription_price; ?> per year</p>

<form action="process_payment.php" method="post">
    <button type="submit" class="button">Subscribe with PayPal</button>
</form>

<?php include './includes/footer.php'; ?>