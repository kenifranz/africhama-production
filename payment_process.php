<?php
// File: payment_process.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/vendor/autoload.php';
require_once $root_path . '/db_connect.php';

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ItemList;
use PayPal\Api\Item;

// PayPal configuration
$paypal_config = require $root_path . '/paypal_config.php';

// Wise configuration
$wise_config = [
    'api_key' => 'YOUR_WISE_API_KEY',
    'profile_id' => 'YOUR_WISE_PROFILE_ID'
];

if (!isset($_SESSION['user_id'])) {
    header("Location: {$base_path}/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? '';
$amount = $_POST['amount'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($payment_method === 'paypal') {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paypal_config['client_id'],
                $paypal_config['client_secret']
            )
        );
        $apiContext->setConfig($paypal_config['settings']);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName('Africhama Payment')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice($amount);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount_obj = new Amount();
        $amount_obj->setCurrency('USD')
            ->setTotal($amount);

        $transaction = new Transaction();
        $transaction->setAmount($amount_obj)
            ->setItemList($itemList)
            ->setDescription('Africhama Payment');

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("{$protocol}://{$host}{$base_path}/payment_success.php")
            ->setCancelUrl("{$protocol}://{$host}{$base_path}/payment_cancel.php");

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {
            $payment->create($apiContext);
            $approvalUrl = $payment->getApprovalLink();
            
            $_SESSION['paypal_payment_id'] = $payment->getId();
            header("Location: {$approvalUrl}");
            exit();
        } catch (Exception $ex) {
            $_SESSION['error_message'] = "PayPal Error: " . $ex->getMessage();
            header("Location: {$base_path}/payment.php");
            exit();
        }
    } elseif ($payment_method === 'wise') {
        $wise_transaction_id = $_POST['wise_transaction_id'] ?? '';
        if (empty($wise_transaction_id)) {
            $_SESSION['error_message'] = "Wise Transaction ID is required.";
            header("Location: {$base_path}/payment.php");
            exit();
        }

        // Here you would typically verify the Wise transaction
        // For now, we'll just record it as pending
        $stmt = $conn->prepare("INSERT INTO manual_payments (user_id, amount, wise_transaction_id, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("ids", $user_id, $amount, $wise_transaction_id);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Wise payment submitted successfully. Please wait for approval.";
        } else {
            $_SESSION['error_message'] = "Error submitting Wise payment. Please try again.";
        }
        header("Location: {$base_path}/payment.php");
        exit();
    } elseif ($payment_method === 'manual') {
        if (isset($_FILES['bank_statement']) && $_FILES['bank_statement']['error'] == 0) {
            $allowed_ext = ["pdf", "jpg", "jpeg", "png"];
            $file_name = $_FILES['bank_statement']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_size = $_FILES['bank_statement']['size'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($file_ext, $allowed_ext)) {
                $_SESSION['error_message'] = "Invalid file format. Please upload a PDF, JPG, JPEG, or PNG file.";
            } elseif ($file_size > $max_size) {
                $_SESSION['error_message'] = "File size must be less than 5MB.";
            } else {
                $upload_dir = $root_path . "/uploads/bank_statements/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $new_file_name = uniqid() . "." . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($_FILES['bank_statement']['tmp_name'], $file_path)) {
                    $stmt = $conn->prepare("INSERT INTO manual_payments (user_id, amount, file_path, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->bind_param("ids", $user_id, $amount, $new_file_name);

                    if ($stmt->execute()) {
                        $_SESSION['success_message'] = "Manual payment submitted successfully. Please wait for approval.";
                    } else {
                        $_SESSION['error_message'] = "Error submitting payment. Please try again.";
                    }
                } else {
                    $_SESSION['error_message'] = "Error uploading file.";
                }
            }
        } else {
            $_SESSION['error_message'] = "Please upload a bank statement.";
        }
        header("Location: {$base_path}/payment.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Invalid payment method";
        header("Location: {$base_path}/payment.php");
        exit();
    }
}
?>