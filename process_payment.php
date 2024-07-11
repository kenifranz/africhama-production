<?php
// File: payment_process.php

use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

require_once 'vendor/autoload.php';
session_start();
require_once 'db_connect.php';

$paypalConfig = require 'paypal_config.php';

$apiContext = new \PayPal\Rest\ApiContext(
    new \PayPal\Auth\OAuthTokenCredential(
        $paypalConfig['client_id'],
        $paypalConfig['client_secret']
    )
);

$apiContext->setConfig($paypalConfig['settings']);

function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $path = rtrim(dirname($script), '/\\');

    // For local development
    if ($host === 'localhost' || strpos($host, '127.0.0.1') !== false) {
        return "{$protocol}{$host}{$path}";
    }

    // For production
    return "{$protocol}{$host}";
}

// Debugging function
function debug($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Item();
        $item->setName('Africhama Yearly Subscription')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setPrice(15.00);

        $itemList = new ItemList();
        $itemList->setItems([$item]);

        $amount = new Amount();
        $amount->setCurrency('USD')
            ->setTotal(15.00);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription('Africhama Yearly Subscription');

        $baseUrl = getBaseUrl();
        $returnUrl = $baseUrl . "/payment_success.php";
        $cancelUrl = $baseUrl . "/payment_cancel.php";

        // Debugging
        echo "Base URL: " . $baseUrl . "<br>";
        echo "Return URL: " . $returnUrl . "<br>";
        echo "Cancel URL: " . $cancelUrl . "<br>";

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        $payment->create($apiContext);
        $approvalUrl = $payment->getApprovalLink();

        // Store payment ID in session for later verification
        $_SESSION['paypal_payment_id'] = $payment->getId();

        // Debugging - comment out for production
        debug($payment);
        exit; // Remove this line when going to production

        header("Location: {$approvalUrl}");
        exit;
    } catch (Exception $ex) {
        $_SESSION['error_message'] = "PayPal Error: " . $ex->getMessage();
        error_log("PayPal Error: " . $ex->getMessage());
        header("Location: payment.php");
        exit;
    }
}

$page_title = "Process Payment";
include './includes/header.php';
?>

<h1>Process Payment</h1>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo htmlspecialchars($_SESSION['error_message']);
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>
<form action="payment_process.php" method="post">
    <button type="submit" class="btn btn-primary">Pay with PayPal</button>
</form>

<?php include './includes/footer.php'; ?>