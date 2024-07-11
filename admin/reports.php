<?php
// File: admin/reports.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Fetch report data
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_transactions FROM transactions");
$stmt->execute();
$total_transactions = $stmt->get_result()->fetch_assoc()['total_transactions'];

$stmt = $conn->prepare("SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'completed'");
$stmt->execute();
$total_revenue = $stmt->get_result()->fetch_assoc()['total_revenue'];

// Fetch monthly revenue data for the chart
$stmt = $conn->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as revenue FROM transactions WHERE status = 'completed' GROUP BY month ORDER BY month DESC LIMIT 12");
$stmt->execute();
$monthly_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Admin Reports";
include $root_path . '/includes/admin_header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<style>
    :root {
        --primary-color: #006400;
        --secondary-color: #008000;
        --accent-color: #32CD32;
        --text-color: #FFFFFF;
        --bg-color: #0A2F0A;
    }

    body {
        background-color: var(--bg-color);
        color: var(--text-color);
    }

    .card {
        background-color: #0D3D0D;
        border: none;
    }

    .card-header {
        background-color: var(--primary-color);
        color: var(--text-color);
    }

    .table {
        color: var(--text-color);
    }

    .table-bordered td, .table-bordered th {
        border-color: #1A5E1A;
    }

    .text-primary {
        color: var(--accent-color) !important;
    }

    .border-left-primary {
        border-left: 0.25rem solid var(--accent-color) !important;
    }

    .border-left-success {
        border-left: 0.25rem solid var(--secondary-color) !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #17a2b8 !important;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-100">Admin Reports</h1>

    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-100"><?php echo number_format($total_users); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-100"><?php echo number_format($total_transactions); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-100">$<?php echo number_format($total_revenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Revenue</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $conn->prepare("SELECT u.username, t.amount, t.created_at FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
                                $stmt->execute();
                                $recent_transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                foreach ($recent_transactions as $transaction):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                    <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($transaction['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart JS
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_reverse($monthly_revenue), 'month')); ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?php echo json_encode(array_column(array_reverse($monthly_revenue), 'revenue')); ?>,
                backgroundColor: 'rgba(50, 205, 50, 0.2)',
                borderColor: 'rgba(50, 205, 50, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(50, 205, 50, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(50, 205, 50, 1)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return '$' + value.toLocaleString();
                        },
                        color: '#FFFFFF'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                },
                x: {
                    ticks: {
                        color: '#FFFFFF'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#FFFFFF'
                    }
                }
            }
        }
    });
</script>

<?php include $root_path . '/includes/admin_footer.php'; ?>