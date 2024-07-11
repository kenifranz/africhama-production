<?php
// File: admin/admin_approve_topups.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topup_id = $_POST['topup_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE manual_topups SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $topup_id);
        $stmt->execute();

        // Get topup details
        $stmt = $conn->prepare("SELECT user_id, friches_amount FROM manual_topups WHERE id = ?");
        $stmt->bind_param("i", $topup_id);
        $stmt->execute();
        $topup = $stmt->get_result()->fetch_assoc();

        // Update user's friches balance
        $stmt = $conn->prepare("UPDATE users SET friches = friches + ? WHERE id = ?");
        $stmt->bind_param("di", $topup['friches_amount'], $topup['user_id']);
        $stmt->execute();

        // Record the transaction
        $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'topup', 'Manual top-up approved')");
        $stmt->bind_param("id", $topup['user_id'], $topup['friches_amount']);
        $stmt->execute();

        // Notify the user
        $notification_message = "Your friches top-up request has been approved.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'topup_approved', '/dashboard.php')");
        $stmt->bind_param("is", $topup['user_id'], $notification_message);
        $stmt->execute();

    } else {
        $stmt = $conn->prepare("UPDATE manual_topups SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $topup_id);
        $stmt->execute();

        // Notify the user
        $stmt = $conn->prepare("SELECT user_id FROM manual_topups WHERE id = ?");
        $stmt->bind_param("i", $topup_id);
        $stmt->execute();
        $topup = $stmt->get_result()->fetch_assoc();

        $notification_message = "Your friches top-up request has been rejected.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'topup_rejected', '/dashboard.php')");
        $stmt->bind_param("is", $topup['user_id'], $notification_message);
        $stmt->execute();
    }

    $_SESSION['success_message'] = "Top-up request has been " . ($action === 'approve' ? 'approved' : 'rejected') . ".";
    header("Location: " . $base_path . "/admin/admin_approve_topups.php");
    exit();
}

// Fetch pending top-ups
$stmt = $conn->prepare("
    SELECT mt.id, mt.user_id, u.username, mt.amount, mt.friches_amount, mt.file_path, mt.created_at
    FROM manual_topups mt
    JOIN users u ON mt.user_id = u.id
    WHERE mt.status = 'pending'
    ORDER BY mt.created_at DESC
");
$stmt->execute();
$pending_topups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Top-ups";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="text-success mb-4">Approve Top-ups</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($pending_topups)): ?>
        <p>No pending top-ups to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Amount (USD)</th>
                    <th>Friches Amount</th>
                    <th>Date</th>
                    <th>Bank Statement</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_topups as $topup): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($topup['username']); ?></td>
                        <td>$<?php echo $topup['amount']; ?></td>
                        <td><?php echo $topup['friches_amount']; ?> Friches</td>
                        <td><?php echo $topup['created_at']; ?></td>
                        <td><a href="<?php echo $base_path; ?>/uploads/topup_statements/<?php echo $topup['file_path']; ?>" target="_blank">View Statement</a></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="topup_id" value="<?php echo $topup['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>