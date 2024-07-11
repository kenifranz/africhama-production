<?php
// File: create_admin.php
require_once 'db_connect.php';

// Set admin user details
$username = 'admin';
$password = 'enigmafx23'; // Replace with a strong password
$email = 'admin@africhama.com';
$first_name = 'Admin';
$last_name = 'User';
$country = 'AdminLand';
$city = 'AdminCity';
$age = 30;
$member_code = 'ADMIN001';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, country, city, age, member_code, role, class, friches) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'admin', 'B', 1000000)");
$stmt->bind_param("sssssssss", $username, $email, $hashed_password, $first_name, $last_name, $country, $city, $age, $member_code);

// Execute the statement
if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error creating admin user: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>