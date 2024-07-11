<?php
// File: payment_success.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';
require_once $root_path . '/vendor/autoload.php';

use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

$paypal_config = require $root_path . '/paypal_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $paypal_config['client_id'],
        $paypal_config['client_secret']
    )
);

$apiContext->setConfig($paypal_config['settings']);

$page_title = "Payment Successful";
include $root_path . '/includes/header.php';

$success = false;
$amount = 0;

if (isset($_GET['paymentId']) && isset($_GET['PayerID'])) {
    $paymentId = $_GET['paymentId'];
    $payerId = $_GET['PayerID'];

    $payment = Payment::get($paymentId, $apiContext);

    $execution = new PaymentExecution();
    $execution->setPayerId($payerId);

    try {
        $result = $payment->execute($execution, $apiContext);
        
        $amount = $result->transactions[0]->amount->total;
        
        $stmt = $conn->prepare("UPDATE users SET payment_status = 'paid', account_balance = account_balance + ? WHERE id = ?");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, transaction_type, status) VALUES (?, ?, 'payment', 'completed')");
        $stmt->bind_param("id", $user_id, $amount);
        $stmt->execute();

        $success = true;
    } catch (Exception $ex) {
        $_SESSION['error_message'] = "Error processing payment: " . $ex->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Payment was not successful.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Africhama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #28a745;
            --primary-dark: #218838;
            --secondary-color: #6c757d;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        .success-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
        }
        .success-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container success-container">
        <?php if ($success): ?>
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="mb-4 text-success">Payment Successful!</h1>
            <p class="lead">Your payment of $<?php echo number_format($amount, 2); ?> has been processed successfully.</p>
            <p>Your account balance has been updated.</p>
            <a href="<?php echo $base_path; ?>/dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
        <?php else: ?>
            <i class="fas fa-times-circle success-icon text-danger"></i>
            <h1 class="mb-4 text-danger">Payment Failed</h1>
            <p class="lead">We're sorry, but there was an issue processing your payment.</p>
            <p><?php echo $_SESSION['error_message'] ?? ''; ?></p>
            <a href="<?php echo $base_path; ?>/payment.php" class="btn btn-primary mt-3">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include $root_path . '/includes/footer.php'; ?>