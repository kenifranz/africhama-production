<?php
// File: register.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';
require_once $root_path . '/includes/language_switcher.php';

$error = '';
$success = '';
$first_name = $last_name = $email = $backup_email = $country = $city = $hobby = $age = $username = $sponsor_code = '';

// Check if there's a referral
$is_referral = isset($_SESSION['referral_code']) && isset($_SESSION['referred_class']) && isset($_SESSION['referrer_id']);

if ($is_referral) {
    $sponsor_code = $_SESSION['referral_code'];
    $invited_class = $_SESSION['referred_class'];
} else {
    $sponsor_code = '';
    $invited_class = 'E'; // Default to E-Class if not a referral
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $backup_email = $_POST['backup_email'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $hobby = $_POST['hobby'] ?? '';
    $age = $_POST['age'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $sponsor_code = $is_referral ? $_SESSION['referral_code'] : ($_POST['sponsor_code'] ?? '');

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($country) || empty($city) || empty($age) || empty($username) || empty($password)) {
        $error = "All fields except Backup Email, Hobby, and Sponsor Code are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($age < 19) {
        $error = "You must be at least 19 years old to register.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Generate unique member code
            $member_code = generateMemberCode($conn);

            // Set default class and status
            $class = $is_referral ? 'Inactive' : (empty($sponsor_code) ? 'ASIM' : 'Inactive');
            $asim_status = $class === 'ASIM' ? 'pending' : NULL;

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Start transaction
            $conn->begin_transaction();

            try {
                // Insert new user into the database
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, backup_email, country, city, hobby, age, username, password, sponsor_code, member_code, class, asim_status, role, invited_class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $role = 'user'; // Set the role explicitly
                $stmt->bind_param("sssssssissssssss", $first_name, $last_name, $email, $backup_email, $country, $city, $hobby, $age, $username, $hashed_password, $sponsor_code, $member_code, $class, $asim_status, $role, $invited_class);
                $stmt->execute();
                $new_user_id = $stmt->insert_id;

                // Handle referral and topline rewards
                if (!empty($sponsor_code) || $is_referral) {
                    $referrer_id = $is_referral ? $_SESSION['referrer_id'] : null;
                    if (!$referrer_id) {
                        $stmt = $conn->prepare("SELECT id FROM users WHERE member_code = ?");
                        $stmt->bind_param("s", $sponsor_code);
                        $stmt->execute();
                        $sponsor_result = $stmt->get_result();
                        if ($sponsor_result->num_rows > 0) {
                            $sponsor = $sponsor_result->fetch_assoc();
                            $referrer_id = $sponsor['id'];
                        }
                    }
                    if ($referrer_id) {
                        $stmt = $conn->prepare("INSERT INTO referrals (referrer_id, referred_id, referred_class) VALUES (?, ?, ?)");
                        $stmt->bind_param("iis", $referrer_id, $new_user_id, $invited_class);
                        $stmt->execute();

                        // Apply topline rewards and deductions
                        applyToplineRewardsAndDeductions($conn, $referrer_id, $invited_class);
                    }
                }

                // If it's an ASIM registration, notify Assistant Admins
                if ($class === 'ASIM') {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'assistant_admin'");
                    $stmt->execute();
                    $assistant_admins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    foreach ($assistant_admins as $admin) {
                        $notification_message = "New ASIM registration: " . $username;
                        sendNotification($conn, $admin['id'], $notification_message, 'asim_approval', '/admin/approve_asim_members.php');
                    }
                }

                // Commit transaction
                $conn->commit();

                if ($class === 'ASIM') {
                    $success = "Registration successful. Your ASIM membership is pending approval by an Assistant Admin.";
                } else {
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;
                    $success = "Registration successful. You will be redirected to the dashboard.";
                    header("refresh:3;url=" . $base_path . "/dashboard.php");
                }

                // Clear referral session data
                unset($_SESSION['referral_code'], $_SESSION['referred_class'], $_SESSION['referrer_id']);
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error = "Registration failed. Please try again. Error: " . $e->getMessage();
            }
        }
    }
}

function generateMemberCode($conn) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code_length = 8;
    
    do {
        $code = '';
        for ($i = 0; $i < $code_length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE member_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $code;
}

function applyToplineRewardsAndDeductions($conn, $topline_id, $invited_class) {
    $friches_deduction = 0;
    $dollar_reward = 0;

    switch ($invited_class) {
        case 'E':
            $friches_deduction = 20;
            $dollar_reward = 10;
            break;
        case 'P':
            $friches_deduction = 70;
            $dollar_reward = 30;
            break;
        case 'B':
            $friches_deduction = 100;
            $dollar_reward = 100;
            break;
    }

    if ($friches_deduction > 0 && $dollar_reward > 0) {
        // Deduct friches from topline
        $stmt = $conn->prepare("UPDATE users SET friches = friches - ? WHERE id = ?");
        $stmt->bind_param("di", $friches_deduction, $topline_id);
        $stmt->execute();

        // Add dollars to topline's account balance
        $stmt = $conn->prepare("UPDATE users SET account_balance = account_balance + ? WHERE id = ?");
        $stmt->bind_param("di", $dollar_reward, $topline_id);
        $stmt->execute();

        // Record friches transaction
        $stmt = $conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'spend', ?)");
        $description = "Friches deduction for inviting " . $invited_class . "-Class member";
        $stmt->bind_param("ids", $topline_id, $friches_deduction, $description);
        $stmt->execute();

        // Record dollar transaction
        $friches_amount = $dollar_reward * 4; // Assuming 1 dollar = 4 friches
        $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, friches_amount, transaction_type, status) VALUES (?, ?, ?, 'earn', 'completed')");
        $stmt->bind_param("idd", $topline_id, $dollar_reward, $friches_amount);
        $stmt->execute();

        // Send notification to topline
        $notification_message = "You received a reward of $" . $dollar_reward . " and spent " . $friches_deduction . " Friches for inviting a " . $invited_class . "-Class member.";
        sendNotification($conn, $topline_id, $notification_message, 'reward', '/dashboard.php');
    }
}

function sendNotification($conn, $user_id, $message, $type, $link) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type, link, is_read) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("isss", $user_id, $message, $type, $link);
    $stmt->execute();

    // Trigger WebSocket notification
    $ws_message = json_encode([
        'type' => 'notification',
        'user_id' => $user_id,
        'message' => $message,
        'link' => $link
    ]);
    // Send $ws_message to WebSocket server (implementation depends on your WebSocket setup)
}

// Fetch list of countries (you may want to store this in a separate file)
$countries = array("Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Democratic Republic of the Congo", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Ivory Coast", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Palestine", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");

$page_title = "Register";
include $root_path . '/includes/header.php';
?>

<div class="container mt-5">
    <h1 class="text-success mb-4"><?php echo _("Register for Africhama"); ?></h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo _($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo _($success); ?></div>
    <?php endif; ?>

    <form action="register.php" method="post" class="needs-validation" novalidate>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label"><?php echo _("First Name:"); ?></label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter your first name."); ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label"><?php echo _("Last Name:"); ?></label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter your last name."); ?>
                    </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label"><?php echo _("Email:"); ?></label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter a valid email address."); ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="backup_email" class="form-label"><?php echo _("Backup Email:"); ?></label>
                <input type="email" id="backup_email" name="backup_email" class="form-control" value="<?php echo htmlspecialchars($backup_email); ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="country" class="form-label"><?php echo _("Country:"); ?></label>
                <select id="country" name="country" class="form-select" required>
                    <option value=""><?php echo _("Select a country"); ?></option>
                    <?php foreach ($countries as $country_option): ?>
                        <option value="<?php echo $country_option; ?>" <?php echo $country === $country_option ? 'selected' : ''; ?>><?php echo _($country_option); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    <?php echo _("Please select a country."); ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="city" class="form-label"><?php echo _("City:"); ?></label>
                <input type="text" id="city" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter your city."); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="hobby" class="form-label"><?php echo _("Hobby:"); ?></label>
                <input type="text" id="hobby" name="hobby" class="form-control" value="<?php echo htmlspecialchars($hobby); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="age" class="form-label"><?php echo _("Age:"); ?></label>
                <input type="number" id="age" name="age" class="form-control" value="<?php echo htmlspecialchars($age); ?>" min="19" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter a valid age (19 or older)."); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username" class="form-label"><?php echo _("Username:"); ?></label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                <div class="invalid-feedback">
                    <?php echo _("Please choose a username."); ?>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label"><?php echo _("Password:"); ?></label>
                <input type="password" id="password" name="password" class="form-control" required>
                <div class="invalid-feedback">
                    <?php echo _("Please enter a password."); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label"><?php echo _("Confirm Password:"); ?></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                <div class="invalid-feedback">
                    <?php echo _("Please confirm your password."); ?>
                </div>
            </div>
            <?php if (!$is_referral): ?>
            <div class="col-md-6 mb-3">
                <label for="sponsor_code" class="form-label"><?php echo _("Sponsor Code (optional):"); ?></label>
                <input type="text" id="sponsor_code" name="sponsor_code" class="form-control" value="<?php echo htmlspecialchars($sponsor_code); ?>">
            </div>
            <?php endif; ?>
        </div>

        <?php if ($is_referral): ?>
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="alert alert-info">
                    <?php echo _("You are registering with a referral link. Your sponsor code is:"); ?> 
                    <strong><?php echo isset($_SESSION['referral_code']) ? htmlspecialchars($_SESSION['referral_code']) : _('Not available'); ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        <?php echo _("I agree to the"); ?> <a href="<?php echo $base_path; ?>/terms.php" target="_blank"><?php echo _("Terms and Conditions"); ?></a>
                    </label>
                    <div class="invalid-feedback">
                        <?php echo _("You must agree to the terms and conditions."); ?>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success"><?php echo _("Register"); ?></button>
    </form>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();
</script>

<?php include $root_path . '/includes/footer.php'; ?>