<?php
// File: admin/edit_user.php
session_start();
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/db_connect.php';

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . $base_path . "/login.php");
    exit();
}

$error = $success = '';
$user = null;

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $class = $_POST['class'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ?, class = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $username, $email, $role, $class, $user_id);

    if ($stmt->execute()) {
        $success = "User updated successfully.";
    } else {
        $error = "Error updating user. Please try again.";
    }
}

$page_title = "Edit User";
include $root_path . '/includes/admin_header.php';
?>

<div class="container mt-4">
    <h1>Edit User</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($user): ?>
        <form action="edit_user.php" method="post">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="assistant_admin" <?php echo $user['role'] === 'assistant_admin' ? 'selected' : ''; ?>>Assistant Admin</option>
                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="class" class="form-label">Class:</label>
                <select id="class" name="class" class="form-select" required>
                    <option value="Inactive" <?php echo $user['class'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="E" <?php echo $user['class'] === 'E' ? 'selected' : ''; ?>>E</option>
                    <option value="P" <?php echo $user['class'] === 'P' ? 'selected' : ''; ?>>P</option>
                    <option value="B" <?php echo $user['class'] === 'B' ? 'selected' : ''; ?>>B</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    <?php else: ?>
        <p>User not found.</p>
    <?php endif; ?>
</div>

<?php include $root_path . '/includes/admin_footer.php'; ?>