<?php
// File: support.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Support";
include 'includes/header.php';
?>

<h1>Africhama Support</h1>

<section class="support-options">
    <h2>Contact Us</h2>
    <p>Our support team is available 24/7 to assist you with any questions or concerns.</p>

    <div class="contact-method">
        <h3>WhatsApp Support</h3>
        <p>Contact us on WhatsApp: <a href="https://wa.me/1234567890">+1 (234) 567-890</a></p>
    </div>

    <div class="contact-method">
        <h3>Email Support</h3>
        <p>Send us an email: <a href="mailto:support@africhama.com">support@africhama.com</a></p>
    </div>

    <div class="contact-method">
        <h3>Phone Support</h3>
        <p>Call our support team: <a href="tel:+12345678901">+1 (234) 567-8901</a></p>
    </div>
</section>

<section class="faq">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-item">
        <h3>How do I upgrade my membership?</h3>
        <p>You can upgrade your membership by visiting the Upgrade page in your dashboard.</p>
    </div>
    <div class="faq-item">
        <h3>When is the yearly maintenance fee due?</h3>
        <p>The yearly maintenance fee is due on the anniversary of your account creation.</p>
    </div>
    <div class="faq-item">
        <h3>How do I earn points?</h3>
        <p>You can earn points by referring new members and through various activities on the platform.</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>