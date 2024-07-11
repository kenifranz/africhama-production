<?php
// File: download_product.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_GET['id'];

// Check if the user has access to the product
$stmt = $conn->prepare("
    SELECT p.file_path, p.title, p.type, u.class
    FROM products p
    JOIN users u ON u.id = ?
    LEFT JOIN user_products up ON up.user_id = ? AND up.product_id = p.id
    WHERE p.id = ? AND (p.type = 'free' OR (u.class != 'Inactive' AND p.type = 'subscription') OR up.id IS NOT NULL)
");
$stmt->bind_param("iii", $user_id, $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    $_SESSION['error_message'] = "You don't have access to this product.";
    header("Location: products.php");
    exit();
}

$file_path = 'uploads/' . $result['file_path'];

if (!file_exists($file_path)) {
    $_SESSION['error_message'] = "File not found.";
    header("Location: products.php");
    exit();
}

// Set appropriate headers for file download
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary");
header("Content-Length: " . filesize($file_path));
header("Content-Disposition: attachment; filename=\"" . $result['title'] . "." . pathinfo($file_path, PATHINFO_EXTENSION) . "\"");

// Output file contents
readfile($file_path);
exit();
?>