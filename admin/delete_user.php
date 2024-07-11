<?php
// File: admin/delete_user.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete records from manual_payments table
        $stmt = $conn->prepare("DELETE FROM manual_payments WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from manual_topups table
        $stmt = $conn->prepare("DELETE FROM manual_topups WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from user_products table
        $stmt = $conn->prepare("DELETE FROM user_products WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from friches_transactions table
        $stmt = $conn->prepare("DELETE FROM friches_transactions WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from notifications table
        $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Update referrals table (set referrer_id to NULL for referred users)
        $stmt = $conn->prepare("UPDATE referrals SET referrer_id = NULL WHERE referrer_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from referrals table where the user is the referred
        $stmt = $conn->prepare("DELETE FROM referrals WHERE referred_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Delete records from subscriptions table
        $stmt = $conn->prepare("DELETE FROM subscriptions WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Finally, delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        $_SESSION['success_message'] = "User deleted successfully.";
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting user: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid user ID.";
}

// Redirect back to the users page
header("Location: " . $base_path . "/admin/users.php");
exit();
?>