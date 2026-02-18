<?php 
session_start();
require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/header.php';
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background: #f8f9fa;
    }

    body {
        font-family: 'Poppins', sans-serif;
        display: flex;
        flex-direction: column;
    }

    nav {
        flex-shrink: 0;
    }

    .hero-modern {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffffff 0%, #f5f7fa 100%);
        padding: 60px 40px;
        overflow: hidden;
    }

    .hero-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        max-width: 1600px;
        width: 100%;
        align-items: center;
    }

    .hero-content h1 {
        font-size: 3.8em;
        font-weight: 700;
        line-height: 1.15;
        margin: 0 0 25px 0;
        color: #1a1a1a;
        letter-spacing: -1px;
    }

    .hero-content h1 .highlight {
        font-style: italic;
        color: #0066cc;
        font-weight: 700;
    }

    .hero-content > p {
        font-size: 1.15em;
        color: #666;
        margin: 0 0 50px 0;
        line-height: 1.6;
        font-weight: 400;
    }

    .search-filters {
        background: white;
        padding: 20px 30px;
        border-radius: 50px;
        display: grid;
        grid-template-columns: repeat(3, 1fr) 130px;
        gap: 20px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        align-items: flex-end;
    }

    .search-filters .filter-group {
        display: flex;
        flex-direction: column;
    }

    .search-filters label {
        font-size: 0.85em;
        color: #666;
        font-weight: 600;
        margin-bottom: 8px;
        text-transform: capitalize;
    }

    .search-filters select,
    .search-filters input {
        padding: 10px 15px;
        border: none;
        background: transparent;
        border-bottom: 1px solid #e0e0e0;
        font-size: 0.95em;
        font-family: 'Poppins', sans-serif;
        color: #333;
    }

    .search-filters select:focus,
    .search-filters input:focus {
        outline: none;
        border-bottom-color: #dc143c;
        background: transparent;
    }

    .search-filters select {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0 center;
        background-size: 20px;
        padding-right: 25px;
    }

    .btn-book {
        background: #dc143c;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95em;
        box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
    }

    .btn-book:hover {
        background: #a01030;
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(220, 20, 60, 0.4);
    }

    .cta-buttons {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .cta-buttons a {
        padding: 10px 25px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 0.95em;
    }

    .btn-primary-cta {
        background: #dc143c;
        color: white;
        box-shadow: 0 4px 15px rgba(220, 20, 60, 0.3);
    }

    .btn-primary-cta:hover {
        background: #a01030;
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(220, 20, 60, 0.4);
    }

    .btn-secondary-cta {
        background: transparent;
        color: #1a1a1a;
        border: 2px solid #1a1a1a;
    }

    .btn-secondary-cta:hover {
        background: #1a1a1a;
        color: white;
    }

    .hero-image {
        position: relative;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-image img {
        height: 100%;
        object-fit: contain;
        filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.1));
    }

    .car-specs {
        position: absolute;
        bottom: 30px;
        right: 20px;
        background: white;
        padding: 25px;
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        width: 300px;
    }

    .car-specs-header {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .car-specs-header img {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        object-fit: cover;
    }

    .car-specs-info h4 {
        margin: 0;
        font-size: 1em;
        color: #1a1a1a;
        font-weight: 600;
    }

    .car-specs-info p {
        margin: 3px 0;
        font-size: 0.85em;
        color: #999;
    }

    .spec-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        padding: 20px 0;
        border-top: 1px solid #f0f0f0;
    }

    .spec-row:first-of-type {
        border-top: none;
        padding-top: 0;
    }

    .spec-item {
        text-align: center;
    }

    .spec-item .value {
        display: block;
        font-size: 1.2em;
        font-weight: 700;
        color: #1a1a1a;
    }

    .spec-item .label {
        display: block;
        font-size: 0.7em;
        color: #999;
        text-transform: uppercase;
        margin-top: 4px;
        font-weight: 600;
    }

    .specs-footer {
        text-align: right;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }

    .specs-footer a {
        color: #dc143c;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9em;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 5px;
    }

    .specs-footer a:hover {
        color: #a01030;
    }

    @media (max-width: 1024px) {
        .hero-container {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .hero-content h1 {
            font-size: 2.8em;
        }

        .hero-image {
            height: 350px;
        }

        .search-filters {
            grid-template-columns: 1fr;
        }

        .car-specs {
            position: static;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .hero-modern {
            padding: 40px 20px;
        }

        .hero-content h1 {
            font-size: 2em;
        }

        .search-filters {
            padding: 20px;
            gap: 15px;
        }

        .search-filters .filter-group {
            margin-bottom: 10px;
        }

        .cta-buttons {
            flex-direction: column;
        }

        .cta-buttons a {
            width: 100%;
            text-align: center;
        }

        .car-specs {
            margin-top: 20px;
        }
    }
</style>

<!-- Hero Section Modern -->
<section class="hero-modern">
    <div class="hero-container">
        <!-- Left Content -->
        <div class="hero-content">
            <h1>Drive the <span class="highlight">Experience</span><br>You Deserve.</h1>
            <p>Professional vehicle maintenance and repair services. Easy booking, no hidden fees.</p>
            
            <!-- Search & Filters -->
            <form method="POST" action="/vehicare_db/appointment.php" class="search-filters">
                <div class="filter-group">
                    <label>Service Type</label>
                    <select name="service_type" required>
                        <option value="">Select Service</option>
                        <?php
                        $serviceResult = $conn->query("SELECT service_id, service_name FROM services LIMIT 8");
                        if ($serviceResult) {
                            while ($service = $serviceResult->fetch_assoc()) {
                                echo "<option value='{$service['service_id']}'>{$service['service_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Vehicle Type</label>
                    <select name="vehicle_type">
                        <option value="">All Vehicles</option>
                        <option value="sedan">Sedan</option>
                        <option value="suv">SUV</option>
                        <option value="truck">Truck</option>
                        <option value="van">Van</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Preferred Date</label>
                    <input type="date" name="appointment_date" required>
                </div>
                <button type="submit" class="btn-book">Book Now</button>
            </form>

            <!-- CTA Buttons for Logged Out Users -->
            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="cta-buttons">
                <a href="/vehicare_db/services.php" class="btn-secondary-cta">View Services</a>
                <a href="/vehicare_db/login.php" class="btn-primary-cta">Sign In</a>
            </div>
            <?php else: ?>
            <div class="cta-buttons">
                <a href="/vehicare_db/client/dashboard.php" class="btn-primary-cta">Go to Dashboard</a>
                <a href="/vehicare_db/services.php" class="btn-secondary-cta">View Services</a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Image with Specs -->
        <div class="hero-image">
            <img src="https://via.placeholder.com/500x400?text=Premium+Vehicle+Maintenance" alt="Premium Car Maintenance">
            
            <!-- Car Specs Card -->
            <div class="car-specs">
                <div class="car-specs-header">
                    <img src="https://via.placeholder.com/70x70?text=Car" alt="Featured Service">
                    <div class="car-specs-info">
                        <h4>Premium Service</h4>
                        <p>Full Maintenance</p>
                    </div>
                </div>
                <div class="spec-row">
                    <div class="spec-item">
                        <span class="value">2hrs</span>
                        <span class="label">Duration</span>
                    </div>
                    <div class="spec-item">
                        <span class="value">$99</span>
                        <span class="label">Price</span>
                    </div>
                    <div class="spec-item">
                        <span class="value">★★★★★</span>
                        <span class="label">Rating</span>
                    </div>
                </div>
                <div class="specs-footer">
                    <a href="/vehicare_db/services.php">View Details →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
