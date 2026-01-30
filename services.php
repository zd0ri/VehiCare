<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    .services-page {
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

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        margin-bottom: 60px;
    }

    .service-card {
        background: white;
        padding: 40px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-align: center;
    }

    .service-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .service-card i {
        font-size: 3.5em;
        color: #0ea5a4;
        margin-bottom: 20px;
    }

    .service-card h3 {
        font-size: 1.4em;
        color: #1a3a52;
        margin: 0 0 15px 0;
        font-weight: 600;
    }

    .service-card p {
        color: #666;
        line-height: 1.6;
        margin: 0 0 20px 0;
    }

    .service-card .btn {
        background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
        color: white;
        border: none;
        padding: 10px 25px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .service-card .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(26, 58, 82, 0.3);
    }

    .features-section {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        padding: 60px 40px;
        border-radius: 16px;
        margin-top: 60px;
    }

    .features-section h2 {
        text-align: center;
        font-size: 2.2em;
        color: #1a3a52;
        margin: 0 0 50px 0;
        font-weight: 700;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 40px;
    }

    .feature-item {
        text-align: center;
    }

    .feature-item i {
        font-size: 3em;
        color: #0ea5a4;
        margin-bottom: 20px;
    }

    .feature-item h3 {
        font-size: 1.2em;
        color: #1a3a52;
        margin: 0 0 12px 0;
        font-weight: 600;
    }

    .feature-item p {
        color: #666;
        line-height: 1.6;
        margin: 0;
    }

    @media (max-width: 768px) {
        .services-page {
            padding: 40px 20px;
        }

        .page-header h1 {
            font-size: 2em;
        }

        .services-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="services-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Our Services</h1>
        <p>Comprehensive vehicle maintenance and repair services designed to keep your car in perfect condition</p>
    </div>

    <!-- Services Grid -->
    <div class="services-grid">
        <div class="service-card">
            <i class="fas fa-cogs"></i>
            <h3>Regular Maintenance</h3>
            <p>Keep your vehicle running smoothly with our comprehensive maintenance services including oil changes, filter replacements, and inspections.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
        <div class="service-card">
            <i class="fas fa-tools"></i>
            <h3>Engine Repair</h3>
            <p>Expert engine diagnostics and repair services. We handle everything from minor tune-ups to major engine overhauls.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
        <div class="service-card">
            <i class="fas fa-brake"></i>
            <h3>Brake Service</h3>
            <p>Safety is our priority. Professional brake inspection, repair, and replacement to ensure your vehicle stops safely.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
        <div class="service-card">
            <i class="fas fa-oil-can"></i>
            <h3>Fluid Services</h3>
            <p>Complete fluid management including oil changes, coolant flushes, transmission fluid, and brake fluid services.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
        <div class="service-card">
            <i class="fas fa-car"></i>
            <h3>Tire Services</h3>
            <p>Professional tire sales, installation, balancing, and rotation. Keep your tires in perfect condition.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
        <div class="service-card">
            <i class="fas fa-battery-half"></i>
            <h3>Battery & Electrical</h3>
            <p>Battery testing, replacement, and electrical system diagnostics. We ensure your vehicle starts every time.</p>
            <button class="btn" onclick="alert('Service details coming soon')">Learn More</button>
        </div>
    </div>

    <!-- Why Choose Us -->
    <div class="features-section">
        <h2>Why Choose VehiCare?</h2>
        <div class="features-grid">
            <div class="feature-item">
                <i class="fas fa-star"></i>
                <h3>Expert Technicians</h3>
                <p>Our certified technicians bring years of experience and expertise to every service.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle"></i>
                <h3>Quality Guarantee</h3>
                <p>We stand behind our work with comprehensive warranties on all services and parts.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-clock"></i>
                <h3>Fast Service</h3>
                <p>Quick turnaround times without compromising on quality. Get back on the road faster.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-wallet"></i>
                <h3>Affordable Pricing</h3>
                <p>Competitive rates with transparent pricing. No hidden fees or surprise charges.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-tools"></i>
                <h3>Modern Equipment</h3>
                <p>State-of-the-art diagnostic and repair equipment for accurate service delivery.</p>
            </div>
            <div class="feature-item">
                <i class="fas fa-headset"></i>
                <h3>Customer Support</h3>
                <p>Dedicated support team ready to answer questions and assist you anytime.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
