<?php
// File: about.php
session_start();
require_once 'db_connect.php';

$page_title = "About Africhama";
include './includes/header.php';
?>

<div class="about-hero">
    <div class="container">
        <h1 class="display-4 text-white mb-4">Empowering Success Through Community</h1>
        <p class="lead text-white mb-4">Discover the story behind Africhama and our mission to transform lives across Africa and beyond.</p>
    </div>
</div>

<div class="container mt-5">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="text-success mb-4">Our Vision</h2>
            <p class="lead">At Africhama, we envision a world where every individual has the opportunity to achieve their full potential, regardless of their background or circumstances.</p>
            <p>We strive to be the catalyst for positive change, empowering people through education, networking, and mutual support. Our platform is more than just a business; it's a movement towards collective growth and prosperity.</p>
        </div>
        <div class="col-lg-6">
            <img src="images/vision-image.jpg" alt="Africhama Vision" class="img-fluid rounded shadow-lg">
        </div>
    </div>

    <div class="row align-items-center mb-5">
        <div class="col-lg-6 order-lg-2">
            <h2 class="text-success mb-4">Our Mission</h2>
            <p class="lead">Africhama is committed to providing a platform that enables individuals to:</p>
            <ul class="list-unstyled">
                <li><i class="fas fa-check-circle text-success me-2"></i> Access quality educational resources</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Build meaningful professional networks</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Participate in a unique gift economy</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Develop essential skills for success</li>
            </ul>
            <p>Through these pillars, we aim to create a self-sustaining ecosystem of growth and mutual support.</p>
        </div>
        <div class="col-lg-6 order-lg-1">
            <img src="images/mission-image.jpg" alt="Africhama Mission" class="img-fluid rounded shadow-lg">
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-success mb-4 text-center">Our Core Values</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-success hover-card">
                        <div class="card-body text-center">
                            <i class="fas fa-handshake fa-3x text-success mb-3"></i>
                            <h3 class="card-title h4">Integrity</h3>
                            <p class="card-text">We uphold the highest standards of honesty and ethical behavior in all our interactions.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-success hover-card">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-3x text-success mb-3"></i>
                            <h3 class="card-title h4">Community</h3>
                            <p class="card-text">We foster a sense of belonging and mutual support among all our members.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 border-success hover-card">
                        <div class="card-body text-center">
                            <i class="fas fa-lightbulb fa-3x text-success mb-3"></i>
                            <h3 class="card-title h4">Innovation</h3>
                            <p class="card-text">We continuously seek new ways to improve and create value for our community.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="text-success mb-4">Our Story</h2>
            <p>Africhama was born out of a simple yet powerful idea: that by coming together and supporting one another, we can achieve greatness. Our founders, inspired by traditional African concepts of community and mutual aid, sought to create a platform that would bring these values into the digital age.</p>
            <p>Since our inception, we've grown from a small group of passionate individuals to a thriving community spanning multiple countries. Our journey is a testament to the power of collective effort and shared vision.</p>
        </div>
        <div class="col-lg-6">
            <img src="images/story-image.jpg" alt="Africhama Story" class="img-fluid rounded shadow-lg">
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-success mb-4 text-center">Join Our Community</h2>
            <p class="text-center lead">Be part of a movement that's changing lives and creating opportunities. Join Africhama today and start your journey towards personal and professional growth.</p>
            <div class="text-center mt-4">
                <a href="register.php" class="btn btn-success btn-lg">Get Started Now</a>
            </div>
        </div>
    </div>
</div>

<style>
    .about-hero {
        background: linear-gradient(rgba(0, 100, 0, 0.8), rgba(0, 100, 0, 0.8)), url('images/about-hero-bg.jpg') no-repeat center center;
        background-size: cover;
        padding: 100px 0;
        margin-bottom: 50px;
    }

    .text-success {
        color: #28a745 !important;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    .hover-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .rounded {
        border-radius: 15px !important;
    }

    .shadow-lg {
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .lead {
        font-size: 1.1rem;
        font-weight: 300;
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 50px 0;
        }
    }
</style>

<?php include './includes/footer.php'; ?>