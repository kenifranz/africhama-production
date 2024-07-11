<?php
// File: admin/create_assistant_admin.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $country = $_POST['country'] ?? '';
    $city = $_POST['city'] ?? '';
    $age = $_POST['age'] ?? '';

    // Validate input
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password) || empty($country) || empty($city) || empty($age)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!is_numeric($age) || $age < 18) {
        $error = "Age must be a number and at least 18.";
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

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new assistant admin into the database
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password, member_code, role, class, country, city, age) VALUES (?, ?, ?, ?, ?, ?, 'assistant_admin', 'B', ?, ?, ?)");
            $stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $username, $hashed_password, $member_code, $country, $city, $age);

            if ($stmt->execute()) {
                $success = "Assistant Admin created successfully.";
            } else {
                $error = "Error creating Assistant Admin. Please try again. Error: " . $stmt->error;
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

$page_title = "Create Assistant Admin";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1 class="text-success mb-4">Create Assistant Admin</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="create_assistant_admin.php" method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" id="last_name" name="last_name" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="confirm_password" class="form-label">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="country" class="form-label">Country:</label>
                <input type="text" id="country" name="country" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="city" class="form-label">City:</label>
                <input type="text" id="city" name="city" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label for="age" class="form-label">Age:</label>
                <input type="number" id="age" name="age" class="form-control" required min="18">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Create Assistant Admin</button>
    </form>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>