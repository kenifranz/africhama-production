<?php
// File: admin/add_product.php
session_start();
require_once '../db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$base_path = '/africhama-production';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $type = $_POST['type'];
    $friches_price = $_POST['friches_price'];

    // File upload handling
    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] == 0) {
        $allowed_ext = array("pdf", "doc", "docx");
        $file_name = $_FILES['product_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['product_file']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Only PDF and DOC files are allowed.";
        } elseif ($file_size > $max_size) {
            $error = "File size must be less than 5MB.";
        } else {
            $upload_dir = "../uploads/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $new_file_name = uniqid() . "." . $file_ext;
            $file_path = $upload_dir . $new_file_name;
            
            if (move_uploaded_file($_FILES['product_file']['tmp_name'], $file_path)) {
                // File uploaded successfully, now insert into database
                $stmt = $conn->prepare("INSERT INTO products (title, description, category, type, friches_price, file_path) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssds", $title, $description, $category, $type, $friches_price, $new_file_name);

                if ($stmt->execute()) {
                    $success = "Product added successfully.";
                } else {
                    $error = "Error adding product: " . $conn->error;
                }
            } else {
                $error = "Error uploading file.";
            }
        }
    } else {
        $error = "No file was uploaded or an error occurred during upload.";
    }
}

$page_title = "Add Product";
include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="text-success">Add New Product</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="add_product.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Title:</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">Category:</label>
            <select id="category" name="category" class="form-select" required>
                <option value="ebook">E-book</option>
                <option value="video">Video</option>
                <option value="article">Article</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type:</label>
            <select id="type" name="type" class="form-select" required>
                <option value="free">Free</option>
                <option value="subscription">Subscription</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="friches_price" class="form-label">Price (Friches):</label>
            <input type="number" id="friches_price" name="friches_price" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label for="product_file" class="form-label">Upload File (PDF or DOC, max 5MB):</label>
            <input type="file" id="product_file" name="product_file" class="form-control" required accept=".pdf,.doc,.docx">
        </div>

        <button type="submit" class="btn btn-success">Add Product</button>
    </form>
</div>

<?php include '../includes/admin_footer.php'; ?>