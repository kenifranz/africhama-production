<?php
// File: index.php
session_start();
require_once 'db_connect.php';

$page_title = "Welcome to Africhama";
include './includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 text-white mb-4">Empower Your Success with Africhama</h1>
                <p class="lead text-white mb-4">Join our community of entrepreneurs and unlock your potential through networking, education, and support.</p>
                <a href="register.php" class="btn btn-light btn-lg">Get Started Today</a>
            </div>
            <div class="col-lg-6">
                <img src="images/hero-image.jpg" alt="Empowering Success" class="img-fluid rounded shadow-lg">
            </div>
        </div>
    </div>
</div>

<div class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Africhama?</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3>Supportive Community</h3>
                    <p>Connect with like-minded entrepreneurs and build lasting relationships that drive success.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-book-open fa-3x text-primary mb-3"></i>
                    <h3>Continuous Learning</h3>
                    <p>Access a wealth of educational resources to enhance your skills and stay ahead in your industry.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h3>Growth Opportunities</h3>
                    <p>Unlock new possibilities for your business with our unique referral system and networking events.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="about-section py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">About Africhama</h2>
                <p class="lead">Africhama is more than just a platform – it's a movement. We're dedicated to empowering entrepreneurs across Africa and beyond, providing the tools, knowledge, and connections needed to thrive in today's competitive business landscape.</p>
                <p>Our name, derived from "Afri" (Africa) and "Chama" (a Swahili word embodying community and collective success), reflects our commitment to fostering a collaborative spirit that drives mutual growth and prosperity.</p>
                <a href="about.php" class="btn btn-primary">Learn More About Us</a>
            </div>
            <div class="col-lg-6">
                <img src="images/about-image.jpg" alt="About Africhama" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

<div class="testimonials-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">What Our Members Say</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <p class="mb-3">"Joining Africhama was the best decision for my business. The community support and resources have been invaluable."</p>
                    <footer class="blockquote-footer">Sarah M., <cite title="Source Title">Tech Entrepreneur</cite></footer>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <p class="mb-3">"The networking opportunities at Africhama have opened doors I never thought possible. My business has grown exponentially!"</p>
                    <footer class="blockquote-footer">John D., <cite title="Source Title">E-commerce Owner</cite></footer>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="testimonial-card">
                    <p class="mb-3">"The educational resources and webinars have helped me stay ahead in my industry. Africhama is a game-changer!"</p>
                    <footer class="blockquote-footer">Emma L., <cite title="Source Title">Marketing Consultant</cite></footer>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="cta-section py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Take Your Success to the Next Level?</h2>
        <p class="lead mb-4">Join Africhama today and start your journey towards entrepreneurial excellence.</p>
        <a href="register.php" class="btn btn-light btn-lg">Sign Up Now</a>
    </div>
</div>

<style>
    .hero-section {
        background: linear-gradient(rgba(0, 100, 0, 0.8), rgba(0, 100, 0, 0.8)), url('images/hero-background.jpg') no-repeat center center;
        background-size: cover;
        padding: 100px 0;
        color: white;
    }

    .feature-card {
        background-color: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out;
    }

    .feature-card:hover {
        transform: translateY(-10px);
    }

    .testimonial-card {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        height: 100%;
    }

    .testimonial-card p {
        font-style: italic;
    }

    .btn-primary {
        background-color: #006400;
        border-color: #006400;
    }

    .btn-primary:hover {
        background-color: #004d00;
        border-color: #004d00;
    }

    .text-primary {
        color: #006400 !important;
    }

    .bg-primary {
        background-color: #006400 !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    h1, h2, h3 {
        font-weight: 700;
    }

    .lead {
        font-weight: 300;
    }

    img {
        max-width: 100%;
        height: auto;
    }
</style>

<?php include './includes/footer.php'; ?>