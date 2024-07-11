<?php
// File: admin/referral_tree.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

// Function to get referrals recursively
function getReferrals($conn, $user_id, $level = 0) {
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.class, r.created_at as referral_date,
               mp.created_at as payment_request_date, mp.status as payment_status,
               TIMESTAMPDIFF(HOUR, mp.created_at, NOW()) as time_since_request
        FROM users u
        JOIN referrals r ON u.id = r.referred_id
        LEFT JOIN manual_payments mp ON u.id = mp.user_id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $referrals = [];
    while ($row = $result->fetch_assoc()) {
        $row['level'] = $level;
        $row['referrals'] = getReferrals($conn, $row['id'], $level + 1);
        $referrals[] = $row;
    }
    return $referrals;
}

// Get all top-level users (users without referrers)
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.class
    FROM users u
    LEFT JOIN referrals r ON u.id = r.referred_id
    WHERE r.referrer_id IS NULL
    ORDER BY u.created_at DESC
");
$stmt->execute();
$top_level_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get referral tree for each top-level user
$referral_tree = [];
foreach ($top_level_users as $user) {
    $user['level'] = 0;
    $user['referrals'] = getReferrals($conn, $user['id'], 1);
    $referral_tree[] = $user;
}

$page_title = "Referral Tree";
include $root_path . '/includes/admin_header.php';
?>

<h2 class="mb-4">Referral Tree</h2>

<div class="referral-tree">
    <?php
    function displayReferralTree($users) {
        foreach ($users as $user) {
            $card_class = 'border-success';
            if ($user['class'] === 'P') {
                $card_class = 'border-primary';
            } elseif ($user['class'] === 'B') {
                $card_class = 'border-warning';
            }
            ?>
            <div class="card mb-3 <?php echo $card_class; ?>" style="margin-left: <?php echo $user['level'] * 40; ?>px;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                    <p class="card-text">
                        <strong>Class:</strong> <?php echo $user['class']; ?><br>
                        <?php if (isset($user['referral_date'])): ?>
                            <strong>Joined:</strong> <?php echo date('Y-m-d H:i', strtotime($user['referral_date'])); ?><br>
                        <?php endif; ?>
                        <?php if (isset($user['payment_request_date'])): ?>
                            <strong>Payment Request:</strong> <?php echo date('Y-m-d H:i', strtotime($user['payment_request_date'])); ?><br>
                            <strong>Payment Status:</strong> <?php echo ucfirst($user['payment_status']); ?><br>
                            <strong>Time Since Request:</strong> 
                            <?php
                            if ($user['payment_status'] === 'approved') {
                                echo $user['time_since_request'] . ' hours (Approved)';
                            } elseif ($user['payment_status'] === 'pending') {
                                echo $user['time_since_request'] . ' hours (Pending)';
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php
            if (!empty($user['referrals'])) {
                displayReferralTree($user['referrals']);
            }
        }
    }

    displayReferralTree($referral_tree);
    ?>
</div>

<style>
    .referral-tree {
        padding: 20px;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-5px);
    }
    .border-success {
        border-left: 5px solid #28a745 !important;
    }
    .border-primary {
        border-left: 5px solid #007bff !important;
    }
    .border-warning {
        border-left: 5px solid #ffc107 !important;
    }
</style>

<?php include $root_path . '/includes/admin_footer.php'; ?>