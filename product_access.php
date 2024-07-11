<?php
// File: product_access.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'] ?? 0; // Assume product ID is passed via GET

// Check if user is super admin
$stmt = $conn->prepare("SELECT is_super_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['is_super_admin']) {
    // Super admin can access all content without restrictions
    $has_access = true;
} else {
    // Normal subscription check for regular users
    $stmt = $conn->prepare("
        SELECT s.status 
        FROM subscriptions s
        JOIN products p ON p.subscription_level <= s.level
        WHERE s.user_id = ? AND s.status = 'active' AND s.end_date >= CURDATE() AND p.id = ?
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $subscription_result = $stmt->get_result();
    $has_access = $subscription_result->num_rows > 0;
}

// Fetch product details
$stmt = $conn->prepare("SELECT title, description, file_path FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

$page_title = "Product Access";
include './includes/header.php';
?>

<h1><?php echo htmlspecialchars($product['title']); ?></h1>

<?php if ($has_access): ?>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <a href="<?php echo htmlspecialchars($product['file_path']); ?>" class="button">Download Product</a>
<?php else: ?>
    <p>You do not have access to this product. Please upgrade your subscription to access this content.</p>
    <a href="upgrade.php" class="button">Upgrade Subscription</a>
<?php endif; ?>

<?php include './includes/footer.php'; ?>