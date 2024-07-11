<?php
$page_title = "Privacy Policy";
$base_path = '/africhama-production';
$root_path = $_SERVER['DOCUMENT_ROOT'] . $base_path;
require_once $root_path . '/includes/admin_header.php';
?>

<div class="container mt-5">
    <h1 class="mb-4 text-success">Privacy Policy</h1>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">1. Information We Collect</h2>
        </div>
        <div class="card-body">
            <p>We collect information you provide directly to us, such as when you create an account, subscribe to our service, or communicate with us. This may include your name, email address, payment information, and any other information you choose to provide.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">2. How We Use Your Information</h2>
        </div>
        <div class="card-body">
            <p>We use the information we collect to provide, maintain, and improve our services, to process your transactions, to send you technical notices and support messages, and to communicate with you about products, services, offers, and events.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">3. Information Sharing and Disclosure</h2>
        </div>
        <div class="card-body">
            <p>We do not share your personal information with third parties except as described in this policy. We may share your information with service providers who perform services on our behalf, or when required by law.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">4. Data Security</h2>
        </div>
        <div class="card-body">
            <p>We take reasonable measures to help protect your personal information from loss, theft, misuse, unauthorized access, disclosure, alteration, and destruction.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">5. Your Choices</h2>
        </div>
        <div class="card-body">
            <p>You may update, correct, or delete your account information at any time by logging into your online account or by contacting us. You may also opt out of receiving promotional communications from us by following the instructions in those messages.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">6. Cookies</h2>
        </div>
        <div class="card-body">
            <p>We use cookies and similar technologies to collect information about your browsing activities over time and across different websites following your use of our services.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">7. Changes to this Policy</h2>
        </div>
        <div class="card-body">
            <p>We may change this privacy policy from time to time. If we make changes, we will notify you by revising the date at the top of the policy and, in some cases, we may provide you with additional notice.</p>
        </div>
    </div>

    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h2 class="card-title h5 mb-0">8. Contact Us</h2>
        </div>
        <div class="card-body">
            <p>If you have any questions about this privacy policy, please contact us at privacy@africhama.com.</p>
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