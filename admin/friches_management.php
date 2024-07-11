<?php
// File: admin/friches_management.php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];
    $action = $_POST['action'];
    $description = $_POST['description'];

    if ($action === 'add') {
        $stmt = $conn->prepare("UPDATE users SET friches = friches + ? WHERE id = ?");
        $transaction_type = 'earn';
    } else {
        $stmt = $conn->prepare("UPDATE users SET friches = friches - ? WHERE id = ?");
        $transaction_type = 'spend';
    }
    
    $stmt->bind_param("di", $amount, $user_id);
    
    if ($stmt->execute()) {
        $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $user_id, $amount, $transaction_type, $description);
        $stmt->execute();
        $success_message = "Friches " . ($action === 'add' ? "added to" : "deducted from") . " user successfully.";
    } else {
        $error_message = "Failed to update Friches. Please try again.";
    }
}

// Fetch all users and their Friches balance
$stmt = $conn->prepare("SELECT id, username, friches FROM users ORDER BY friches DESC");
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Friches Management";
include '../includes/admin_header.php';
?>

<h1>Friches Management</h1>

<?php if (isset($success_message)): ?>
    <div class="success-message"><?php echo $success_message; ?></div>
<?php elseif (isset($error_message)): ?>
    <div class="error-message"><?php echo $error_message; ?></div>
<?php endif; ?>

<form action="friches_management.php" method="post">
    <h2>Add/Deduct Friches</h2>
    <div class="form-group">
        <label for="user_id">Select User:</label>
        <select name="user_id" id="user_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']) . ' (Current: ' . $user['friches'] . ' Friches)'; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" required>
    </div>
    <div class="form-group">
        <label for="action">Action:</label>
        <select name="action" id="action" required>
            <option value="add">Add</option>
            <option value="deduct">Deduct</option>
        </select>
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" required>
    </div>
    <button type="submit" class="button">Submit</button>
</form>

<h2>Users Friches Balance</h2>
<table>
    <thead>
        <tr>
            <th>Username</th>
            <th>Friches Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo $user['friches']; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/admin_footer.php'; ?>