<?php
// File: admin/approve_downline_registrations.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and is an upline
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user is an upline
$stmt = $conn->prepare("SELECT COUNT(*) as downline_count FROM referrals WHERE referrer_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$is_upline = ($result->fetch_assoc()['downline_count'] > 0);

if (!$is_upline) {
    header("Location: " . $base_path . "/dashboard.php");
    exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $downline_id = $_POST['downline_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $downline_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Downline registration approved successfully.";
            
            // Notify the downline
            $notification_message = "Your registration has been approved by your upline.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'registration_approved', '/dashboard.php')");
            $stmt->bind_param("is", $downline_id, $notification_message);
            $stmt->execute();
        } else {
            $_SESSION['error_message'] = "Error approving downline registration.";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $downline_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "Downline registration rejected successfully.";
            
            // Notify the downline
            $notification_message = "Your registration has been rejected by your upline.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'registration_rejected', '/dashboard.php')");
            $stmt->bind_param("is", $downline_id, $notification_message);
            $stmt->execute();
        } else {
            $_SESSION['error_message'] = "Error rejecting downline registration.";
        }
    }
}

// Fetch pending downline registrations
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.country, u.city, u.created_at
    FROM users u
    JOIN referrals r ON u.id = r.referred_id
    WHERE r.referrer_id = ? AND u.status = 'pending'
    ORDER BY u.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve Downline Registrations";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Approve Downline Registrations</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_registrations)): ?>
        <p>No pending downline registrations to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_registrations as $registration): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registration['username']); ?></td>
                        <td><?php echo htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($registration['email']); ?></td>
                        <td><?php echo htmlspecialchars($registration['country']); ?></td>
                        <td><?php echo htmlspecialchars($registration['city']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($registration['created_at'])); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="downline_id" value="<?php echo $registration['id']; ?>">
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