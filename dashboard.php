<?php
// File: dashboard.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details and update session
$stmt = $conn->prepare("SELECT username, class, payment_status, friches, account_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update session with latest user data
$_SESSION['class'] = $user['class'];
$_SESSION['payment_status'] = $user['payment_status'];

// Fetch referral count
$stmt = $conn->prepare("SELECT COUNT(*) as referral_count FROM referrals WHERE referrer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$referral_count = $stmt->get_result()->fetch_assoc()['referral_count'];

// Fetch recent transactions
$stmt = $conn->prepare("
    SELECT amount, transaction_type, created_at 
    FROM transactions 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Dashboard";
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
            --success-color: #5cb85c;
            --info-color: #5bc0de;
            --warning-color: #f0ad4e;
            --danger-color: #d9534f;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            color: var(--dark-color);
        }
        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .card-body {
            padding: 20px;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        .table td {
            vertical-align: middle;
        }
        .dashboard-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .quick-action-btn {
            text-align: left;
            padding: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: translateX(5px);
        }
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4 text-success">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <?php if ($user['payment_status'] !== 'paid'): ?>
            <div class="alert alert-warning alert-custom">
                <h4 class="alert-heading">Account Inactive</h4>
                <p>Your account is currently inactive. Please make a payment to activate your account and unlock all features.</p>
                <hr>
                <a href="<?php echo $base_path; ?>/payment.php" class="btn btn-primary">Activate Account</a>
            </div>
        <?php else: ?>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Class</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-graduation-cap dashboard-icon text-success"></i>
                            <p class="card-text fs-4"><?php echo $user['class']; ?>-Class</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Friches Balance</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-coins dashboard-icon text-warning"></i>
                            <p class="card-text fs-4"><?php echo number_format($user['friches'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Account Balance</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-dollar-sign dashboard-icon text-info"></i>
                            <p class="card-text fs-4">$<?php echo number_format($user['account_balance'], 2); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Referrals</h5>
                        </div>
                        <div class="card-body text-center">
                            <i class="fas fa-users dashboard-icon text-primary"></i>
                            <p class="card-text fs-4"><?php echo $referral_count; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Transactions</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_transactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                            <td><span class="badge bg-<?php echo getTransactionBadgeColor($transaction['transaction_type']); ?>"><?php echo ucfirst($transaction['transaction_type']); ?></span></td>
                                            <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-3">
                                <?php if ($user['class'] !== 'B'): ?>
                                    <a href="<?php echo $base_path; ?>/upgrade.php" class="btn btn-success quick-action-btn">
                                        <i class="fas fa-arrow-up me-2"></i>Upgrade Class
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo $base_path; ?>/topup_friches.php" class="btn btn-warning quick-action-btn">
                                    <i class="fas fa-coins me-2"></i>Top Up Friches
                                </a>
                                <a href="<?php echo $base_path; ?>/products.php" class="btn btn-info quick-action-btn">
                                    <i class="fas fa-shopping-cart me-2"></i>View Products
                                </a>
                                <a href="<?php echo $base_path; ?>/my_network.php" class="btn btn-primary quick-action-btn">
                                    <i class="fas fa-network-wired me-2"></i>My Network
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getTransactionBadgeColor($type) {
    switch ($type) {
        case 'earn':
            return 'success';
        case 'spend':
            return 'danger';
        case 'purchase':
            return 'info';
        case 'referral':
            return 'primary';
        default:
            return 'secondary';
    }
}
?>