<?php
$page_title = "Help Center";
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
include $root_path . '/includes/header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4 text-success">Help Center</h1>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="card-title h5 mb-0">Getting Started</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#create-account" class="text-success">How to create an account</a></li>
                        <li class="list-group-item"><a href="#login" class="text-success">How to log in</a></li>
                        <li class="list-group-item"><a href="#profile" class="text-success">Setting up your profile</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="card-title h5 mb-0">Account Management</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#change-password" class="text-success">Changing your password</a></li>
                        <li class="list-group-item"><a href="#update-info" class="text-success">Updating account information</a></li>
                        <li class="list-group-item"><a href="#subscription" class="text-success">Managing your subscription</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h2 class="card-title h5 mb-0">Using Africhama</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#courses" class="text-success">Accessing courses</a></li>
                        <li class="list-group-item"><a href="#networking" class="text-success">Networking features</a></li>
                        <li class="list-group-item"><a href="#referrals" class="text-success">Referral program</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h2 id="create-account" class="text-success">How to create an account</h2>
        <p>To create an account, click on the "Register" button in the top right corner of the homepage. Fill out the registration form with your personal information and choose a unique username and password. After submitting the form, you'll receive a confirmation email to activate your account.</p>

        <h2 id="login" class="text-success">How to log in</h2>
        <p>To log in, click on the "Login" button in the top right corner of the homepage. Enter your username and password, then click "Submit". If you've forgotten your password, click on the "Forgot Password" link to reset it.</p>

        <h2 id="profile" class="text-success">Setting up your profile</h2>
        <p>After logging in, go to your profile page by clicking on your username in the top right corner. Click on "Edit Profile" to add or update your information, including your profile picture, bio, and social media links.</p>

        <h2 id="change-password" class="text-success">Changing your password</h2>
        <p>To change your password, go to your account settings page. Look for the "Change Password" section, enter your current password, then your new password twice to confirm. Click "Save" to update your password.</p>

        <h2 id="update-info" class="text-success">Updating account information</h2>
        <p>You can update your account information, such as your email address or phone number, from your account settings page. Make the necessary changes and click "Save" to update your information.</p>

        <h2 id="subscription" class="text-success">Managing your subscription</h2>
        <p>To manage your subscription, go to your account settings and look for the "Subscription" section. Here you can view your current plan, upgrade or downgrade your subscription, or cancel it if needed.</p>

        <h2 id="courses" class="text-success">Accessing courses</h2>
        <p>To access courses, go to the "Courses" section in the main menu. Browse available courses and click on one to view its details. If you're subscribed or the course is free, you can start learning immediately.</p>

        <h2 id="networking" class="text-success">Networking features</h2>
        <p>Africhama offers various networking features. You can connect with other users, join groups related to your interests, and participate in forum discussions. Explore the "Network" section to discover these features.</p>

        <h2 id="referrals" class="text-success">Referral program</h2>
        <p>Our referral program allows you to earn rewards by inviting others to join Africhama. Find your unique referral link in your account dashboard and share it with friends and colleagues. You'll receive rewards for each person who signs up using your link.</p>
    </div>

    <div class="mt-5 bg-light p-4 rounded">
        <h2 class="text-success">Still need help?</h2>
        <p>If you couldn't find the answer to your question, please contact our support team:</p>
        <ul class="list-unstyled">
            <li><i class="fas fa-envelope text-success"></i> Email: support@africhama.com</li>
            <li><i class="fas fa-phone text-success"></i> Phone: +1 (234) 567-8900</li>
            <li><i class="fas fa-comment text-success"></i> Live Chat: Available on our website during business hours</li>
        </ul>
    </div>
</div>

<style>
    body {
        background-color: #f0fff0;
    }
    .card {
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        transform: translateY(-5px);
    }
    .text-success {
        color: #28a745 !important;
    }
    .bg-success {
        background-color: #28a745 !important;
    }
    .border-success {
        border-color: #28a745 !important;
    }
    a.text-success:hover {
        color: #1e7e34 !important;
        text-decoration: none;
    }
    .list-group-item {
        border-color: #28a745;
    }
</style>

<?php include $root_path . '/includes/footer.php'; ?>