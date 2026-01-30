<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    .about-page {
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

    .about-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        margin-bottom: 80px;
    }

    .about-section.reverse {
        grid-template-columns: 1fr 1fr;
    }

    .about-section.reverse > div:first-child {
        order: 2;
    }

    .about-section.reverse > div:last-child {
        order: 1;
    }

    .about-section h2 {
        font-size: 2.2em;
        color: #1a3a52;
        margin: 0 0 30px 0;
        font-weight: 700;
    }

    .about-section p {
        color: #666;
        line-height: 1.8;
        margin: 0 0 20px 0;
        font-size: 1em;
    }

    .about-section ul {
        list-style: none;
        padding: 0;
        margin: 30px 0 0 0;
    }

    .about-section ul li {
        padding: 10px 0 10px 35px;
        position: relative;
        color: #666;
        line-height: 1.8;
    }

    .about-section ul li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #0ea5a4;
        font-weight: 700;
        font-size: 1.2em;
    }

    .about-image {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        border-radius: 12px;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .about-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        margin-top: 60px;
        padding: 60px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
        border-radius: 12px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.8em;
        font-weight: 700;
        color: #0ea5a4;
        margin-bottom: 10px;
    }

    .stat-label {
        color: #666;
        font-size: 1em;
    }

    .team-section {
        margin-top: 80px;
    }

    .team-section h2 {
        text-align: center;
        font-size: 2.2em;
        color: #1a3a52;
        margin: 0 0 50px 0;
        font-weight: 700;
    }

    .team-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .team-member {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        text-align: center;
    }

    .team-member:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .team-member-image {
        width: 100%;
        height: 250px;
        background: linear-gradient(135deg, #0ea5a4 0%, #087373 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4em;
        color: white;
    }

    .team-member-info {
        padding: 25px;
    }

    .team-member h3 {
        font-size: 1.2em;
        color: #1a3a52;
        margin: 0 0 5px 0;
        font-weight: 600;
    }

    .team-member p {
        color: #0ea5a4;
        font-weight: 600;
        margin: 0 0 15px 0;
        font-size: 0.9em;
    }

    .team-member .bio {
        color: #666;
        line-height: 1.6;
        font-size: 0.9em;
        margin: 0;
    }

    @media (max-width: 768px) {
        .about-page {
            padding: 40px 20px;
        }

        .page-header h1 {
            font-size: 2em;
        }

        .about-section {
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .about-section.reverse > div:first-child {
            order: initial;
        }

        .about-section.reverse > div:last-child {
            order: initial;
        }

        .about-section h2 {
            font-size: 1.8em;
        }

        .about-image {
            height: 300px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            padding: 40px 20px;
        }

        .team-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="about-page">
    <!-- Page Header -->
    <div class="page-header">
        <h1>About VehiCare</h1>
        <p>Your trusted partner in vehicle maintenance and repair since 2015</p>
    </div>

    <!-- About Section 1 -->
    <div class="about-section">
        <div>
            <h2>Our Story</h2>
            <p>VehiCare was founded on the belief that every vehicle deserves expert care and attention. Starting from a small garage in Taguig, we've grown into a trusted automotive service provider serving thousands of satisfied customers.</p>
            <p>With over 8 years of experience in the industry, our team has consistently delivered high-quality maintenance and repair services, building a reputation for reliability, honesty, and excellence.</p>
            <ul>
                <li>Professional certified technicians</li>
                <li>State-of-the-art equipment</li>
                <li>Transparent pricing</li>
                <li>Warranty on all services</li>
            </ul>
        </div>
        <div class="about-image">
            <img src="https://via.placeholder.com/500x400?text=Our+Workshop" alt="VehiCare Workshop">
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number">8+</div>
            <div class="stat-label">Years of Experience</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">5000+</div>
            <div class="stat-label">Satisfied Customers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">20+</div>
            <div class="stat-label">Professional Technicians</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">100%</div>
            <div class="stat-label">Customer Satisfaction</div>
        </div>
    </div>

    <!-- About Section 2 -->
    <div class="about-section reverse">
        <div>
            <h2>Our Mission</h2>
            <p>To provide reliable, affordable, and professional automotive maintenance and repair services that exceed customer expectations.</p>
            <p>We believe in treating every customer with respect and providing honest advice about their vehicle's maintenance needs. Our goal is to be your trusted partner in keeping your vehicle safe, reliable, and in perfect condition.</p>
            <ul>
                <li>Quality service at competitive prices</li>
                <li>Fast turnaround times</li>
                <li>Experienced and friendly staff</li>
                <li>Modern diagnostic equipment</li>
            </ul>
        </div>
        <div class="about-image">
            <img src="https://via.placeholder.com/500x400?text=Expert+Service" alt="Expert Service">
        </div>
    </div>

    <!-- Team Section -->
    <div class="team-section">
        <h2>Meet Our Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <div class="team-member-image">👨‍🔧</div>
                <div class="team-member-info">
                    <h3>John Manager</h3>
                    <p>Service Manager</p>
                    <p class="bio">With 15 years of experience, John oversees all service operations and ensures customer satisfaction.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="team-member-image">👨‍💼</div>
                <div class="team-member-info">
                    <h3>Mark Smith</h3>
                    <p>Lead Technician</p>
                    <p class="bio">Certified master mechanic with expertise in engine repair and diagnostics.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="team-member-image">👩‍🔧</div>
                <div class="team-member-info">
                    <h3>Sarah Johnson</h3>
                    <p>Specialist</p>
                    <p class="bio">Brake and electrical systems specialist with 10 years of expertise.</p>
                </div>
            </div>
            <div class="team-member">
                <div class="team-member-image">👨‍💼</div>
                <div class="team-member-info">
                    <h3>Mike Davis</h3>
                    <p>Customer Service</p>
                    <p class="bio">Dedicated to ensuring every customer has a smooth and pleasant experience.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
