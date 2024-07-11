<?php
// File: notifications.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'mark_read' && isset($_POST['notification_id'])) {
        $notification_id = $_POST['notification_id'];
        $user_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $notification_id, $user_id);
        $result = $stmt->execute();

        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
        }
        exit;
    }
}

// Fetch notifications for admin and assistant admin
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stmt = $conn->prepare("
    SELECT n.id, n.message, n.created_at, n.type, n.link
    FROM notifications n
    WHERE n.user_id = ? AND n.is_read = 0
    AND (? IN ('admin', 'assistant_admin') OR n.type NOT IN ('asim_approval', 'topup_approval'))
    ORDER BY n.created_at DESC
    LIMIT 5
");
$stmt->bind_param("is", $user_id, $role);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode(['success' => true, 'notifications' => $notifications]);
exit;