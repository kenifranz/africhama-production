<?php
// File: profile.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update user information
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $backup_email = $_POST['backup_email'];
    $country = $_POST['country'];
    $city = $_POST['city'];
    $hobby = $_POST['hobby'];

    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, backup_email = ?, country = ?, city = ?, hobby = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $backup_email, $country, $city, $hobby, $user_id);
    $stmt->execute();

    // Update available days
    $available_days = [];
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    foreach ($days as $day) {
        if (isset($_POST[$day]) && !empty($_POST[$day])) {
            $available_days[$day] = $_POST[$day];
        }
    }

    $available_days_json = json_encode($available_days);

    $stmt = $conn->prepare("UPDATE users SET available_days = ? WHERE id = ?");
    $stmt->bind_param("si", $available_days_json, $user_id);
    $stmt->execute();

    $success_message = "Your profile has been updated successfully.";
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Correctly handle potentially NULL available_days
$available_days = [];
if (!is_null($user['available_days'])) {
    $decoded_days = json_decode($user['available_days'], true);
    if (is_array($decoded_days)) {
        $available_days = $decoded_days;
    }
}

// Fetch list of countries
$countries = array("Afghanistan", "Albania", "Algeria", /* ... add all countries ... */ "Zimbabwe");

$page_title = "User Profile";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="my-4">User Profile</h1>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name:</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="backup_email" class="form-label">Backup Email:</label>
                            <input type="email" id="backup_email" name="backup_email" value="<?php echo htmlspecialchars($user['backup_email']); ?>" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Country:</label>
                            <select id="country" name="country" class="form-select" required>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo $country; ?>" <?php echo ($user['country'] == $country) ? 'selected' : ''; ?>><?php echo $country; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label">City:</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="hobby" class="form-label">Hobby:</label>
                            <input type="text" id="hobby" name="hobby" value="<?php echo htmlspecialchars($user['hobby']); ?>" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Available Days</div>
                <div class="card-body">
                    <form action="profile.php" method="post">
                        <?php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        foreach ($days as $day):
                            $day_lower = strtolower($day);
                        ?>
                            <div class="mb-3">
                                <label for="<?php echo $day_lower; ?>" class="form-label"><?php echo $day; ?>:</label>
                                <input type="text" id="<?php echo $day_lower; ?>" name="<?php echo $day_lower; ?>" value="<?php echo htmlspecialchars($available_days[$day_lower] ?? ''); ?>" class="form-control" placeholder="e.g., 9:00 AM - 5:00 PM">
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">Update Availability</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>