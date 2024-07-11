<?php
// File: admin/approve_asim_members.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and is an Assistant Admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET class = 'Inactive', asim_status = 'approved' WHERE id = ? AND class = 'ASIM' AND asim_status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Send notification to the approved user
            $notification_message = "Your ASIM membership has been approved. You are now an Inactive member.";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link) VALUES (?, ?, 'asim_approved', '/dashboard.php')");
            $stmt->bind_param("is", $user_id, $notification_message);
            $stmt->execute();

            $_SESSION['success_message'] = "ASIM member approved successfully.";
        } else {
            $_SESSION['error_message'] = "Error approving ASIM member. Member may have already been approved.";
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET asim_status = 'rejected' WHERE id = ? AND class = 'ASIM' AND asim_status = 'pending'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['success_message'] = "ASIM member rejected successfully.";
        } else {
            $_SESSION['error_message'] = "Error rejecting ASIM member. Member may have already been processed.";
        }
    }

    header("Location: " . $base_path . "/admin/approve_asim_members.php");
    exit();
}

// Fetch pending ASIM members
$stmt = $conn->prepare("
    SELECT id, username, email, first_name, last_name, country, city, age
    FROM users
    WHERE class = 'ASIM' AND asim_status = 'pending'
    ORDER BY created_at DESC
");
$stmt->execute();
$pending_members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Approve ASIM Members";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Approve ASIM Members</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <?php if (empty($pending_members)): ?>
        <p>No pending ASIM members to approve.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Country</th>
                    <th>City</th>
                    <th>Age</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_members as $member): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                        <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                        <td><?php echo htmlspecialchars($member['country']); ?></td>
                        <td><?php echo htmlspecialchars($member['city']); ?></td>
                        <td><?php echo htmlspecialchars($member['age']); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
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