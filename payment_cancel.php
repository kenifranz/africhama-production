<?php
// File: payment_cancel.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;

$page_title = "Payment Cancelled";
include $root_path . '/includes/header.php';
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
        .cancel-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
        }
        .cancel-icon {
            font-size: 5rem;
            color: var(--secondary-color);
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
    <div class="container cancel-container">
        <i class="fas fa-ban cancel-icon"></i>
        <h1 class="mb-4 text-secondary">Payment Cancelled</h1>
        <p class="lead">Your payment has been cancelled.</p>
        <p>If you experienced any issues or have questions, please contact our support team.</p>
        <a href="<?php echo $base_path; ?>/payment.php" class="btn btn-primary mt-3">Try Again</a>
        <a href="<?php echo $base_path; ?>/dashboard.php" class="btn btn-secondary mt-3 ml-2">Go to Dashboard</a>
    </div>
</body>
</html>

<?php include $root_path . '/includes/footer.php'; ?>