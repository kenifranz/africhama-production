<?php
// File: products.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's class
$stmt = $conn->prepare("SELECT class FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();
$user_class = $user_result['class'];

$stmt = $conn->prepare("
    SELECT id, title, description, category, type, friches_price
    FROM products
    ORDER BY type, category, title
");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Products";
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1 class="text-success mb-4">Africhama Products</h1>

    <div class="alert alert-info mb-4">
        <h4 class="alert-heading">Your current class: <?php echo $user_class; ?></h4>
    </div>

    <?php if ($user_class === 'Inactive'): ?>
        <div class="alert alert-warning mb-4">
            <h4 class="alert-heading">Unlock Premium Content</h4>
            <p>Subscribe now to access our full range of educational materials!</p>
            <a href="<?php echo $base_path; ?>/upgrade.php" class="btn btn-primary">Upgrade Your Membership</a>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?php echo ucfirst($product['category']); ?></span>
                        <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="fw-bold">Price: $<?php echo number_format($product['friches_price'], 2); ?></p>
                        <?php if ($product['type'] === 'free'): ?>
                            <a href="<?php echo $base_path; ?>/download_product.php?id=<?php echo $product['id']; ?>" class="btn btn-success">Download</a>
                        <?php elseif ($user_class !== 'Inactive'): ?>
                            <a href="<?php echo $base_path; ?>/purchase_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Purchase</a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Upgrade to Access</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include $root_path . '/includes/footer.php'; ?>