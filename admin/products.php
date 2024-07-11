<?php
// File: admin/products.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Fetch products
$stmt = $conn->prepare("SELECT id, title, category, type, friches_price, created_at FROM products ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Products";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="text-success">Manage Products</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <a href="<?php echo $base_path; ?>/admin/add_product.php" class="btn btn-success mb-3">Add New Product</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Type</th>
                <th>Price (Friches)</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo htmlspecialchars($product['title']); ?></td>
                    <td><?php echo $product['category']; ?></td>
                    <td><?php echo $product['type']; ?></td>
                    <td><?php echo $product['friches_price']; ?></td>
                    <td><?php echo $product['created_at']; ?></td>
                    <td>
                        <a href="<?php echo $base_path; ?>/admin/edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="<?php echo $base_path; ?>/admin/delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>