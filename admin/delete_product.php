<?php
// File: admin/delete_product.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete related records in user_products table
        $stmt = $conn->prepare("DELETE FROM user_products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Then, delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // If we get here, it means both queries were successful
        $conn->commit();
        $_SESSION['success_message'] = "Product and related records deleted successfully.";
    } catch (Exception $e) {
        // An error occurred; rollback the transaction
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid product ID.";
}

// Redirect back to the products page
header("Location: " . $base_path . "/admin/products.php");
exit();
?>