<?php
// File: admin/transactions.php
session_start();
require_once '../db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch transactions
$stmt = $conn->prepare("
    SELECT t.id, u.username, t.amount, t.friches_amount, t.transaction_type, t.status, t.created_at 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Manage Transactions";
include '../includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="text-success">Manage Transactions</h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Amount (USD)</th>
                <th>Amount (Friches)</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?php echo $transaction['id']; ?></td>
                    <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                    <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                    <td><?php echo $transaction['friches_amount']; ?></td>
                    <td><?php echo $transaction['transaction_type']; ?></td>
                    <td><?php echo $transaction['status']; ?></td>
                    <td><?php echo $transaction['created_at']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/admin_footer.php'; ?>