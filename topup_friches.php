<?php
// File: topup_friches.php
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

// Fetch user details
$stmt = $conn->prepare("SELECT username, friches, class, asim_status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$page_title = "Top Up Friches";
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
        .topup-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }
        .payment-option {
            border: 2px solid var(--primary-color);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            background-color: var(--light-color);
        }
        .payment-option.selected {
            background-color: var(--primary-color);
            color: white;
        }
        .btn-success {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-success:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        #manual-payment-form, #wise-payment-form {
            display: none;
        }
        .bank-details, .wise-details {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            margin-top: 1rem;
        }
        .bank-details h4, .wise-details h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .bank-details p, .wise-details p {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container topup-container">
        <h1 class="mb-4 text-center text-success">Top Up Friches</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <p>Current Friches Balance: <?php echo number_format($user['friches'], 2); ?> Friches</p>
        <p>Your current class: <?php echo $user['class']; ?></p>

        <?php if ($user['class'] === 'ASIM' && $user['asim_status'] !== 'approved'): ?>
            <div class="alert alert-warning">
                Your ASIM membership is pending approval. You can purchase Friches, but you won't be able to use them until your membership is approved.
            </div>
        <?php endif; ?>

        <form action="process_topup.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="topup_amount" class="form-label">Amount to Top Up (USD)</label>
                <input type="number" class="form-control" id="topup_amount" name="topup_amount" min="1" step="0.01" required>
                <small class="form-text text-muted">1 USD = 4 Friches</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Payment Method</label>
                <div class="payment-option" onclick="selectPaymentMethod('paypal')">
                    <input type="radio" name="payment_method" id="paypal" value="paypal" required>
                    <label for="paypal"><i class="fab fa-paypal"></i> PayPal</label>
                </div>
                <div class="payment-option" onclick="selectPaymentMethod('wise')">
                    <input type="radio" name="payment_method" id="wise" value="wise" required>
                    <label for="wise"><i class="fas fa-exchange-alt"></i> Wise (TransferWise)</label>
                </div>
                <div class="payment-option" onclick="selectPaymentMethod('manual')">
                    <input type="radio" name="payment_method" id="manual" value="manual" required>
                    <label for="manual"><i class="fas fa-university"></i> Manual Bank Transfer</label>
                </div>
            </div>
            <div id="wise-payment-form">
                <div class="wise-details">
                    <h4>Wise Transfer Details</h4>
                    <p><strong>Recipient:</strong> Africhama Ltd</p>
                    <p><strong>Email:</strong> payments@africhama.com</p>
                    <p><strong>Reference:</strong> FRICHES-[Your Username]</p>
                    <p>Please initiate the transfer using the Wise platform and provide the transaction ID below.</p>
                </div>
                <div class="mb-3 mt-3">
                    <label for="wise_transaction_id" class="form-label">Wise Transaction ID:</label>
                    <input type="text" id="wise_transaction_id" name="wise_transaction_id" class="form-control">
                </div>
            </div>
            <div id="manual-payment-form">
                <div class="bank-details">
                    <h4>Bank Transfer Details</h4>
                    <p><strong>Account name:</strong> Hifadhi Current Account(CDI)</p>
                    <p><strong>Account number:</strong> 8700447047500</p>
                    <p><strong>Bank name:</strong> Standard Chartered Bank</p>
                    <p><strong>Sort code:</strong> 02084</p>
                    <p><strong>IBAN:</strong> 8700447047500</p>
                    <p><strong>BIC / Swift:</strong> SCBLKENXXXX</p>
                </div>
                <div class="mb-3 mt-3">
                    <label for="bank_statement" class="form-label">Upload Bank Statement (PDF, JPG, JPEG, or PNG):</label>
                    <input type="file" id="bank_statement" name="bank_statement" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100">Submit Top-up Request</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.getElementById(method).checked = true;
            document.getElementById(method).closest('.payment-option').classList.add('selected');
            
            document.getElementById('wise-payment-form').style.display = 'none';
            document.getElementById('manual-payment-form').style.display = 'none';
            
            if (method === 'wise') {
                document.getElementById('wise-payment-form').style.display = 'block';
            } else if (method === 'manual') {
                document.getElementById('manual-payment-form').style.display = 'block';
            }
        }
    </script>
</body>
</html>

<?php include $root_path . '/includes/footer.php'; ?>