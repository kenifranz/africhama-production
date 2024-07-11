<?php
// File: admin/dashboard.php
session_start();
require_once '../db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$page_title = "Admin Dashboard";
include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h1>Welcome to the Admin Dashboard</h1>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">User Management</h5>
                    <p class="card-text">Manage user accounts, roles, and permissions.</p>
                    <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Product Management</h5>
                    <p class="card-text">Add, edit, or remove products from the platform.</p>
                    <a href="manage_products.php" class="btn btn-primary">Manage Products</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Transaction History</h5>
                    <p class="card-text">View and manage all transactions on the platform.</p>
                    <a href="transactions.php" class="btn btn-primary">View Transactions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>