<?php
// File: referral.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';
require_once $root_path . '/includes/language_switcher.php';

// Simple logging function
function logError($message) {
    $logFile = $_SERVER['DOCUMENT_ROOT'] . '/africhama-production/logs/referral_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Log the incoming request
logError("Referral request received. Code: " . ($_GET['code'] ?? 'Not set') . ", Class: " . ($_GET['class'] ?? 'Not set'));

if (isset($_GET['code']) && isset($_GET['class'])) {
    $referral_code = $_GET['code'];
    $referred_class = $_GET['class'];
    
    // Validate the referral code and class
    $stmt = $conn->prepare("SELECT id, class, friches FROM users WHERE member_code = ?");
    $stmt->bind_param("s", $referral_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $referrer = $result->fetch_assoc();
        $referrer_id = $referrer['id'];
        $referrer_class = $referrer['class'];
        
        logError("Referrer found. ID: $referrer_id, Class: $referrer_class");
        
        // Check if the referrer is allowed to refer the specified class
        $allowed = false;
        switch ($referrer_class) {
            case 'B':
                $allowed = true;
                break;
            case 'P':
                $allowed = ($referred_class == 'E' || $referred_class == 'P');
                break;
            case 'E':
                $allowed = ($referred_class == 'E');
                break;
        }
        
        if ($allowed) {
            // Store the referral information in the session
            $_SESSION['referral_code'] = $referral_code;
            $_SESSION['referred_class'] = $referred_class;
            $_SESSION['referrer_id'] = $referrer_id;
            
            logError("Referral allowed. Stored in session. Redirecting to registration.");
            
            // Redirect to the registration page
            header("Location: " . $base_path . "/register.php");
            exit();
        } else {
            logError("Referral not allowed. Referrer class: $referrer_class, Referred class: $referred_class");
            $_SESSION['error_message'] = _("Invalid referral class for this referrer.");
        }
    } else {
        logError("Invalid referral code: $referral_code");
        $_SESSION['error_message'] = _("Invalid referral code.");
    }
    
    header("Location: " . $base_path . "/index.php");
    exit();
} else {
    logError("Missing referral code or class in URL parameters.");
    // If no referral code or class is provided, redirect to the home page
    header("Location: " . $base_path . "/index.php");
    exit();
}

// If we reach this point, it means there was an error
$page_title = _("Referral Error");
include $root_path . '/includes/header.php';
?>

<div class="container mt-4">
    <h1 class="text-danger"><?php echo _("Referral Error"); ?></h1>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <p><?php echo _("There was an error processing your referral link. Please check the link and try again, or contact support if the issue persists."); ?></p>
    
    <a href="<?php echo $base_path; ?>/index.php" class="btn btn-primary"><?php echo _("Return to Home Page"); ?></a>
</div>

<?php include $root_path . '/includes/footer.php'; ?>