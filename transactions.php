<?php
// File: transactions.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's transactions
$stmt = $conn->prepare("
    SELECT amount, transaction_type, description, created_at
    FROM friches_transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Transaction History";
include './includes/header.php';
?>

<div class="container mt-4">
    <h1 class="text-success mb-4">Transaction History</h1>

    <?php if (empty($transactions)): ?>
        <p>You don't have any transactions yet.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($transaction['created_at'])); ?></td>
                        <td><?php echo ucfirst($transaction['transaction_type']); ?></td>
                        <td><?php echo number_format($transaction['amount'], 2); ?> Friches</td>
                        <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include './includes/footer.php'; ?>