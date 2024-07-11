<?php
// File: my_network.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's details
$stmt = $conn->prepare("SELECT username, member_code, class FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch user's topline (referrer)
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.class, r.created_at as referral_date
    FROM users u
    JOIN referrals r ON u.id = r.referrer_id
    WHERE r.referred_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$topline = $stmt->get_result()->fetch_assoc();

// Fetch user's direct referrals (downlines)
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.first_name, u.last_name, u.class, u.payment_status, r.created_at as referral_date
    FROM referrals r
    JOIN users u ON r.referred_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$downlines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch pending payments from downlines
$stmt = $conn->prepare("
    SELECT mp.id, mp.user_id, u.username, mp.amount, mp.file_path, mp.created_at
    FROM manual_payments mp
    JOIN users u ON mp.user_id = u.id
    WHERE mp.topline_id = ? AND mp.status = 'pending'
    ORDER BY mp.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pending_payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Generate referral links
$referral_links = [];
switch ($user['class']) {
    case 'B':
        $referral_links['B'] = "http://{$_SERVER['HTTP_HOST']}{$base_path}/referral.php?code=" . $user['member_code'] . "&class=B";
        // Intentional fall-through
    case 'P':
        $referral_links['P'] = "http://{$_SERVER['HTTP_HOST']}{$base_path}/referral.php?code=" . $user['member_code'] . "&class=P";
        // Intentional fall-through
    case 'E':
        $referral_links['E'] = "http://{$_SERVER['HTTP_HOST']}{$base_path}/referral.php?code=" . $user['member_code'] . "&class=E";
        break;
}

$page_title = "My Network";
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1 class="text-center mb-5">My Network</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h2 class="card-title h5 mb-0">Your Referral Links</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($referral_links as $class => $link): ?>
                        <div class="mb-3">
                            <h3 class="h6">Class <?php echo $class; ?></h3>
                            <div class="input-group">
                                <input type="text" class="form-control" id="referral-link-<?php echo $class; ?>" value="<?php echo $link; ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('referral-link-<?php echo $class; ?>')">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h2 class="card-title h5 mb-0">Your Topline</h2>
                </div>
                <div class="card-body">
                    <?php if ($topline): ?>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Username:</strong>
                                <span><?php echo htmlspecialchars($topline['username']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Class:</strong>
                                <span class="badge bg-primary rounded-pill"><?php echo $topline['class']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>Referral Date:</strong>
                                <span><?php echo date('Y-m-d', strtotime($topline['referral_date'])); ?></span>
                            </li>
                        </ul>
                    <?php else: ?>
                        <p class="card-text">You don't have a topline (you were not referred by anyone).</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">Your Downlines</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($downlines)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Status</th>
                                <th>Referral Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($downlines as $downline): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($downline['username']); ?></td>
                                    <td><?php echo htmlspecialchars($downline['first_name'] . ' ' . $downline['last_name']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $downline['class']; ?></span></td>
                                    <td>
                                        <?php if ($downline['payment_status'] === 'paid'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($downline['referral_date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="card-text">You don't have any downlines yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h2 class="card-title h5 mb-0">Pending Downline Payments</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($pending_payments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Bank Statement</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_payments as $payment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                    <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo $base_path; ?>/uploads/bank_statements/<?php echo $payment['file_path']; ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-alt"></i> View
                                        </a>
                                    </td>
                                    <td>
                                        <form method="post" action="<?php echo $base_path; ?>/approve_payments.php" class="d-inline">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="card-text">No pending payments from downlines to approve.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
<script>
function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    
    var tooltip = document.createElement("div");
    tooltip.className = "tooltip";
    tooltip.innerHTML = "Copied!";
    document.body.appendChild(tooltip);
    
    setTimeout(function() {
        document.body.removeChild(tooltip);
    }, 2000);
}
</script>
<style>
.tooltip {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #333;
    color: #fff;
    padding: 10px;
    border-radius: 5px;
    z-index: 1000;
}
</style>

<?php include $root_path . '/includes/footer.php'; ?>