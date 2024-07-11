<?php
// File: admin/index.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Fetch key metrics
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total_users'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
$stmt->execute();
$total_products = $stmt->get_result()->fetch_assoc()['total_products'];

$stmt = $conn->prepare("SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'completed'");
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$total_revenue = $result['total_revenue'] ?? 0;

// Fetch user growth data for the last 7 days
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, COUNT(*) as new_users
    FROM users
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
");
$stmt->execute();
$user_growth_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch revenue data for the last 7 days
$stmt = $conn->prepare("
    SELECT DATE(created_at) as date, SUM(amount) as daily_revenue
    FROM transactions
    WHERE status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
");
$stmt->execute();
$revenue_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Admin Dashboard";
include $root_path . '/includes/admin_header.php';
?>

<div class="container-fluid mt-4">
    <h1 class="text-success mb-4">Africhama Admin Dashboard</h1>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-4"><?php echo number_format($total_users); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text display-4"><?php echo number_format($total_products); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text display-4">$<?php echo number_format($total_revenue, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    User Growth (Last 7 Days)
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Revenue (Last 7 Days)
                </div>
                <div class="card-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Recent Activities
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">New user registration: John Doe</li>
                        <li class="list-group-item">Product purchase: Introduction to Africhama</li>
                        <li class="list-group-item">Payment received: $30.00</li>
                        <li class="list-group-item">New ASIM member approval pending</li>
                        <li class="list-group-item">Class upgrade: User123 from E to P</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Quick Actions
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo $base_path; ?>/admin/users.php" class="btn btn-outline-success">Manage Users</a>
                        <a href="<?php echo $base_path; ?>/admin/products.php" class="btn btn-outline-success">Manage Products</a>
                        <a href="<?php echo $base_path; ?>/admin/transactions.php" class="btn btn-outline-success">View Transactions</a>
                        <a href="<?php echo $base_path; ?>/admin/reports.php" class="btn btn-outline-success">Generate Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Growth Chart
var userCtx = document.getElementById('userGrowthChart').getContext('2d');
var userGrowthChart = new Chart(userCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($user_growth_data, 'date')); ?>,
        datasets: [{
            label: 'New Users',
            data: <?php echo json_encode(array_column($user_growth_data, 'new_users')); ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.2)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Revenue Chart
var revenueCtx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($revenue_data, 'date')); ?>,
        datasets: [{
            label: 'Daily Revenue',
            data: <?php echo json_encode(array_column($revenue_data, 'daily_revenue')); ?>,
            backgroundColor: 'rgba(40, 167, 69, 0.6)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        return '$' + value.toFixed(2);
                    }
                }
            }
        }
    }
});
</script>

<?php include $root_path . '/includes/admin_footer.php'; ?>