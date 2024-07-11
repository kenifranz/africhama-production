<?php
// File: includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'assistant_admin')) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Fetch notifications for admin
$notifications = [];
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT id, message, created_at, type, link, is_read
    FROM notifications
    WHERE user_id = ? AND is_read = 0
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " - Africhama Admin" : "Africhama Admin"; ?></title>
    <link rel="icon" type="image/png" href="<?php echo $base_path; ?>/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #006400;
            --secondary-color: #008000;
            --accent-color: #32CD32;
            --text-color: #FFFFFF;
            --bg-color: #F0FFF0;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: #333;
        }
        
        .admin-wrapper {
            display: flex;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 20px;
        }
        
        .sidebar .nav-link {
            color: var(--text-color);
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        
        .content {
            flex: 1;
            padding: 20px;
        }
        
        .navbar {
            background-color: var(--primary-color);
            padding: 10px 20px;
        }
        
        .navbar-brand {
            color: var(--text-color) !important;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .navbar .nav-link {
            color: var(--text-color) !important;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .dropdown-item {
            padding: 10px 20px;
        }
        
        .dropdown-item:hover {
            background-color: var(--accent-color);
            color: var(--text-color);
        }
        
        .badge-notify {
            background-color: var(--accent-color);
            position: absolute;
            top: 0;
            right: 5px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        @media (max-width: 767.98px) {
            .admin-wrapper {
                position: relative;
                overflow-x: hidden;
            }
            .sidebar {
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .admin-wrapper.sidebar-collapsed .sidebar {
                transform: translateX(0);
            }
            .content {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <nav class="sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/users.php">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/create_assistant_admin.php">
                            <i class="fas fa-users"></i> Create Assistant Admin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'approve_asim_members.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/approve_asim_members.php">
                            <i class="fas fa-user-check"></i> Approve ASIM Members
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'approve_payments.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/approve_payments.php">
                            <i class="fas fa-money-check-alt"></i> Approve Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/products.php">
                            <i class="fas fa-box"></i> Manage Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/transactions.php">
                            <i class="fas fa-exchange-alt"></i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/reports.php">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="<?php echo $base_path; ?>/admin/settings.php">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <div class="content">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container-fluid">
                    <a class="navbar-brand" href="<?php echo $base_path; ?>/admin/index.php">Africhama Admin</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>/admin/profile.php">Profile</a></li>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>/logout.php">Logout</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-bell"></i>
                                    <?php if (count($notifications) > 0): ?>
                                        <span class="badge badge-notify"><?php echo count($notifications); ?></span>
                                    <?php endif; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                                    <?php if (empty($notifications)): ?>
                                        <li><a class="dropdown-item" href="#">No new notifications</a></li>
                                    <?php else: ?>
                                        <li><h6 class="dropdown-header">Notifications</h6></li>
                                        <?php foreach ($notifications as $notification): ?>
                                            <li>
                                                <a class="dropdown-item notification-item" href="<?php echo $base_path . $notification['link']; ?>" data-notification-id="<?php echo $notification['id']; ?>">
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                    <small class="text-muted d-block"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                                                </a>
                                                <button class="btn btn-sm btn-link text-danger clear-notification" data-notification-id="<?php echo $notification['id']; ?>">Clear</button>
                                            </li>
                                        <?php endforeach; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-center" href="#" id="clearAllNotifications">Clear All</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="container-fluid mt-4">
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            ' . htmlspecialchars($_SESSION['success_message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ' . htmlspecialchars($_SESSION['error_message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <!-- Main content will be inserted here -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Clear individual notification
    document.querySelectorAll('.clear-notification').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const notificationId = this.getAttribute('data-notification-id');
            clearNotification(notificationId);
        });
    });

    // Clear all notifications
    document.getElementById('clearAllNotifications').addEventListener('click', function(e) {
        e.preventDefault();
        clearAllNotifications();
    });

    function clearNotification(notificationId) {
        fetch('<?php echo $base_path; ?>/admin/clear_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_one&notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`).closest('li');
                notificationItem.remove();
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function clearAllNotifications() {
        fetch('<?php echo $base_path; ?>/admin/clear_notifications.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear_all'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationDropdown = document.getElementById('notificationsDropdown').nextElementSibling;
                notificationDropdown.innerHTML = '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateNotificationCount() {
        const badge = document.querySelector('.badge-notify');
        const notificationItems = document.querySelectorAll('.notification-item');
        if (notificationItems.length === 0) {
            if (badge) badge.remove();
        } else {
            if (badge) {
                badge.textContent = notificationItems.length;
            } else {
                const newBadge = document.createElement('span');
                newBadge.className = 'badge badge-notify';
                newBadge.textContent = notificationItems.length;
                document.getElementById('notificationsDropdown').appendChild(newBadge);
            }
        }
    }
});
</script>

</body>
</html>