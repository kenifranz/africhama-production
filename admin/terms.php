<?php
$page_title = "Terms of Service";
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/includes/admin_header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4 text-success">Terms of Service</h1>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">1. Acceptance of Terms</h2>
        </div>
        <div class="card-body">
            <p>By accessing or using Africhama's services, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">2. Description of Service</h2>
        </div>
        <div class="card-body">
            <p>Africhama provides an online platform for educational content and networking opportunities. We offer both free and subscription-based services.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">3. User Accounts</h2>
        </div>
        <div class="card-body">
            <p>You are responsible for maintaining the confidentiality of your account information and password. You agree to notify us immediately of any unauthorized use of your account.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">4. User Conduct</h2>
        </div>
        <div class="card-body">
            <p>You agree not to use the service for any unlawful purpose or in any way that interrupts, damages, or impairs the service. You must not attempt to gain unauthorized access to any part of the service or its related systems.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">5. Intellectual Property</h2>
        </div>
        <div class="card-body">
            <p>All content provided on Africhama is the property of Africhama or its content suppliers and is protected by international copyright laws.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">6. Payment and Refunds</h2>
        </div>
        <div class="card-body">
            <p>Payments for subscription services are non-refundable. We reserve the right to change our fees at any time, with notice provided on our website.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">7. Termination</h2>
        </div>
        <div class="card-body">
            <p>We reserve the right to terminate or suspend your account and access to the service at our sole discretion, without notice, for conduct that we believe violates these Terms of Service or is harmful to other users, us, or third parties, or for any other reason.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">8. Changes to Terms</h2>
        </div>
        <div class="card-body">
            <p>We reserve the right to modify these Terms of Service at any time. We will provide notice of significant changes by posting on our website. Your continued use of the service after such modifications will constitute your acknowledgment of the modified Terms of Service.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">9. Contact Information</h2>
        </div>
        <div class="card-body">
            <p>If you have any questions about these Terms, please contact us at legal@africhama.com.</p>
        </div>
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
</style>

<?php require_once $root_path . '/includes/admin_footer.php'; ?>