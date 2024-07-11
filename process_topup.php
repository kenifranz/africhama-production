<?php
// File: process_topup.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topup_amount = $_POST['topup_amount'];
    $friches_amount = $topup_amount * 4; // 1 USD = 4 Friches
    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'paypal') {
        // Redirect to PayPal payment page (you'll need to implement this)
        $_SESSION['topup_amount'] = $topup_amount;
        $_SESSION['friches_amount'] = $friches_amount;
        header("Location: " . $base_path . "/paypal_payment.php");
        exit();
    } elseif ($payment_method === 'wise') {
        $wise_transaction_id = $_POST['wise_transaction_id'];
        // Process Wise payment (you'll need to implement the verification)
        // For now, we'll just record it as pending
        $stmt = $conn->prepare("INSERT INTO manual_topups (user_id, amount, friches_amount, wise_transaction_id, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("idds", $user_id, $topup_amount, $friches_amount, $wise_transaction_id);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Your Wise top-up request has been submitted successfully. Please wait for approval.";
        } else {
            $_SESSION['error_message'] = "Error submitting Wise top-up request. Please try again.";
        }
    } elseif ($payment_method === 'manual') {
        // Handle manual payment
        if (isset($_FILES['bank_statement'])) {
            $file_name = $_FILES['bank_statement']['name'];
            $file_tmp = $_FILES['bank_statement']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = array("pdf", "jpg", "jpeg", "png");

            if (in_array($file_ext, $allowed_ext)) {
                $upload_dir = $root_path . "/uploads/topup_statements/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_file_name = uniqid() . "." . $file_ext;
                move_uploaded_file($file_tmp, $upload_dir . $new_file_name);

                // Record the manual top-up request in the database
                $stmt = $conn->prepare("INSERT INTO manual_topups (user_id, amount, friches_amount, file_path, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("idds", $user_id, $topup_amount, $friches_amount, $new_file_name);
                $stmt->execute();

                $_SESSION['success_message'] = "Your top-up request has been submitted successfully. Please wait for admin approval.";
            } else {
                $_SESSION['error_message'] = "Invalid file format. Please upload a PDF, JPG, JPEG, or PNG file.";
            }
        } else {
            $_SESSION['error_message'] = "Please upload a bank statement.";
        }
    } else {
        $_SESSION['error_message'] = "Invalid payment method selected.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: " . $base_path . "/topup_friches.php");
exit();
?>