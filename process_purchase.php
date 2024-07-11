<?php
// File: process_purchase.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['product_id'])) {
    header("Location: products.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Fetch user's current Friches balance and product price
    $stmt = $conn->prepare("SELECT friches FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_friches = $stmt->get_result()->fetch_assoc()['friches'];

    $stmt = $conn->prepare("SELECT title, friches_price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($user_friches < $product['friches_price']) {
        throw new Exception("Insufficient Friches balance.");
    }

    // Deduct Friches from user's balance
    $new_balance = $user_friches - $product['friches_price'];
    $stmt = $conn->prepare("UPDATE users SET friches = ? WHERE id = ?");
    $stmt->bind_param("di", $new_balance, $user_id);
    $stmt->execute();

    // Record the transaction
    $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'purchase', ?)");
    $description = "Purchase of product: " . $product['title'];
    $stmt->bind_param("ids", $user_id, $product['friches_price'], $description);
    $stmt->execute();

    // Grant access to the product (you might want to create a separate table for user_products)
    $stmt = $conn->prepare("INSERT INTO user_products (user_id, product_id, purchase_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    $_SESSION['success_message'] = "Product purchased successfully!";
    header("Location: products.php");
    exit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error_message'] = "Purchase failed: " . $e->getMessage();
    header("Location: products.php");
    exit();
}
?>