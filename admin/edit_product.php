<?php
// File: admin/edit_product.php
session_start();
require_once '../db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$error = $success = '';
$product = null;

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $type = $_POST['type'];
    $friches_price = $_POST['friches_price'];

    $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, category = ?, type = ?, friches_price = ? WHERE id = ?");
    $stmt->bind_param("ssssdi", $title, $description, $category, $type, $friches_price, $product_id);

    if ($stmt->execute()) {
        $success = "Product updated successfully.";
    } else {
        $error = "Error updating product. Please try again.";
    }
}

$page_title = "Edit Product";
include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h1>Edit Product</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($product): ?>
        <form action="edit_product.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            
            <div class="mb-3">
                <label for="title" class="form-label">Title:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($product['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category:</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="ebook" <?php echo $product['category'] === 'ebook' ? 'selected' : ''; ?>>E-book</option>
                    <option value="video" <?php echo $product['category'] === 'video' ? 'selected' : ''; ?>>Video</option>
                    <option value="article" <?php echo $product['category'] === 'article' ? 'selected' : ''; ?>>Article</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">Type:</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="free" <?php echo $product['type'] === 'free' ? 'selected' : ''; ?>>Free</option>
                    <option value="subscription" <?php echo $product['type'] === 'subscription' ? 'selected' : ''; ?>>Subscription</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="friches_price" class="form-label">Price (Friches):</label>
                <input type="number" id="friches_price" name="friches_price" class="form-control" value="<?php echo $product['friches_price']; ?>" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    <?php else: ?>
        <p>Product not found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/admin_footer.php'; ?>