<?php
// File: paypal_payment.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['amount'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$amount = $_GET['amount'];

// Here you would integrate with PayPal's API to create a payment
// This is a placeholder for the actual PayPal integration
$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
$paypal_email = "your-paypal-business-email@example.com";
$return_url = "http://yourdomain.com/payment_success.php";
$cancel_url = "http://yourdomain.com/payment_cancel.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayPal Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>PayPal Payment</h1>
        <p>You are about to pay $<?php echo $amount; ?> via PayPal.</p>
        <form action="<?php echo $paypal_url; ?>" method="post">
            <input type="hidden" name="business" value="<?php echo $paypal_email; ?>">
            <input type="hidden" name="cmd" value="_xclick">
            <input type="hidden" name="item_name" value="Africhama Membership">
            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="return" value="<?php echo $return_url; ?>">
            <input type="hidden" name="cancel_return" value="<?php echo $cancel_url; ?>">
            <button type="submit" class="btn btn-primary">Proceed to PayPal</button>
        </form>
    </div>
</body>
</html>