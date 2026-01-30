<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    .contact-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 60px 20px;
    }

    .page-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .page-header h1 {
        font-size: 2.8em;
        color: #1a3a52;
        margin: 0 0 20px 0;
        font-weight: 700;
    }

    .page-header p {
        font-size: 1.1em;
        color: #666;
        max-width: 600px;
        margin: 0 auto;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-bottom: 60px;
    }

    .contact-item {
        background: white;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s ease;
    }

    .contact-item:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .contact-item i {
        font-size: 3em;
        color: #0ea5a4;
        margin-bottom: 20px;
    }

    .contact-item h3 {
        font-size: 1.3em;
        color: #1a3a52;
        margin: 0 0 15px 0;
        font-weight: 600;
    }

    .contact-item p {
        color: #666;
        line-height: 1.8;
        margin: 0;
    }

    .contact-item a {
        color: #0ea5a4;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .contact-item a:hover {
        color: #087373;
    }

    .form-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        padding: 60px 40px;
        border-radius: 16px;
    }

    .form-section h2 {
        text-align: center;
        font-size: 2.2em;
        color: #1a3a52;
        margin: 0 0 40px 0;
        font-weight: 700;
    }

    .contact-form {
        max-width: 700px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 12px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #0ea5a4;
        box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 120px;
    }

    .btn-submit {
        background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
        color: white;
        border: none;
        padding: 12px 40px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(26, 58, 82, 0.3);
    }

    .map-section {
        margin-top: 60px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .map-section iframe {
        width: 100%;
        height: 400px;
        border: none;
    }

    @media (max-width: 768px) {
        .contact-page {
            padding: 40px 20px;
        }

        .page-header h1 {
            font-size: 2em;
        }

        .form-section {
            padding: 40px 20px;
        }

        .contact-form {
            padding: 30px;
        }

        .contact-grid {
            grid-template-columns: 1fr;
        }

        .map-section iframe {
            height: 300px;
        }
    }
</style>

<div class="contact-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Contact Us</h1>
        <p>Have questions? We're here to help. Get in touch with our team today.</p>
    </div>

    <!-- Contact Information Grid -->
    <div class="contact-grid">
        <div class="contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <h3>Location</h3>
            <p>123 Auto Service Lane<br>Taguig City, Philippines 1630<br><a href="#">View on Map</a></p>
        </div>
        <div class="contact-item">
            <i class="fas fa-phone"></i>
            <h3>Phone</h3>
            <p><a href="tel:+63945551234">+63 (945) 551-234</a><br><a href="tel:+63945551235">+63 (945) 551-235</a><br>Available 24/7</p>
        </div>
        <div class="contact-item">
            <i class="fas fa-envelope"></i>
            <h3>Email</h3>
            <p><a href="mailto:info@vehicare.com">info@vehicare.com</a><br><a href="mailto:support@vehicare.com">support@vehicare.com</a></p>
        </div>
        <div class="contact-item">
            <i class="fas fa-clock"></i>
            <h3>Hours</h3>
            <p>Monday - Friday: 8:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 4:00 PM<br>Sunday: Closed</p>
        </div>
        <div class="contact-item">
            <i class="fas fa-car"></i>
            <h3>Services</h3>
            <p>Oil Changes • Engine Repair<br>Brake Service • Tire Services<br>Battery & Electrical</p>
        </div>
        <div class="contact-item">
            <i class="fas fa-users"></i>
            <h3>Follow Us</h3>
            <p><a href="#"><i class="fab fa-facebook"></i> Facebook</a><br><a href="#"><i class="fab fa-twitter"></i> Twitter</a><br><a href="#"><i class="fab fa-instagram"></i> Instagram</a></p>
        </div>
    </div>

    <!-- Contact Form Section -->
    <div class="form-section">
        <h2>Send us a Message</h2>
        <form class="contact-form" method="POST" action="mailto:info@vehicare.com">
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </div>

    <!-- Map Section -->
    <div class="map-section">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.5220480325316!2d121.0457!3d14.5569!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397ca2aaa9f0d43%3A0xc3ae9b1b7f3f3f3f!2sTaguig%2C%20Metro%20Manila!5e0!3m2!1sen!2sph!4v1234567890" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
