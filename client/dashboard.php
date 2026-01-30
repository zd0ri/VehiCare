<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    body {
        font-family: 'Poppins', sans-serif;
    }

    .client-dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        background: #f5f7fa;
        min-height: 100vh;
    }

    /* Welcome Header */
    .welcome-header {
        background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
        color: white;
        padding: 50px 40px;
        border-radius: 16px;
        margin-bottom: 40px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .welcome-header h1 {
        font-size: 2.5em;
        margin: 0 0 10px 0;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .welcome-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 1.1em;
    }

    /* Stats Section */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ffc107, #ff9800);
    }

    .stat-card:nth-child(2)::before {
        background: linear-gradient(90deg, #27ae60, #16a34a);
    }

    .stat-card:nth-child(3)::before {
        background: linear-gradient(90deg, #3498db, #2980b9);
    }

    .stat-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .stat-info h3 {
        margin: 0;
        color: #666;
        font-size: 13px;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .stat-number {
        font-size: 2.5em;
        margin: 12px 0 0 0;
        color: #1a3a52;
        font-weight: 700;
    }

    .stat-icon {
        font-size: 3.5em;
        opacity: 0.15;
        margin-left: 20px;
    }

    .stat-card:nth-child(1) .stat-icon {
        color: #ffc107;
    }

    .stat-card:nth-child(2) .stat-icon {
        color: #27ae60;
    }

    .stat-card:nth-child(3) .stat-icon {
        color: #3498db;
    }

    /* Actions Section */
    .actions-section {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 40px;
    }

    .section-title {
        font-size: 1.8em;
        margin: 0 0 30px 0;
        color: #1a3a52;
        font-weight: 700;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .action-btn {
        padding: 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        min-height: 140px;
        cursor: pointer;
    }

    .action-btn i {
        font-size: 2.5em;
    }

    .action-btn.primary {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        box-shadow: 0 6px 15px rgba(255, 152, 0, 0.3);
    }

    .action-btn.primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(255, 152, 0, 0.4);
    }

    .action-btn.secondary {
        background: #f8f9fa;
        color: #1a3a52;
        border: 2px solid #e0e5ea;
    }

    .action-btn.secondary:hover {
        background: #1a3a52;
        color: white;
        border-color: #1a3a52;
        transform: translateY(-3px);
    }

    /* Info Section */
    .info-section {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .info-card {
        padding: 25px;
        background: linear-gradient(135deg, #f5f7fa 0%, #e8eef5 100%);
        border-radius: 10px;
        text-align: center;
    }

    .info-card i {
        font-size: 2.5em;
        color: #1a3a52;
        margin-bottom: 15px;
        display: block;
    }

    .info-card h3 {
        margin: 0 0 10px 0;
        color: #1a3a52;
        font-size: 1.2em;
    }

    .info-card p {
        margin: 0;
        color: #666;
        font-size: 0.95em;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .welcome-header {
            padding: 30px 20px;
        }

        .welcome-header h1 {
            font-size: 1.8em;
        }

        .stats-container {
            grid-template-columns: 1fr;
        }

        .actions-section,
        .info-section {
            padding: 25px;
        }

        .section-title {
            font-size: 1.4em;
        }
    }
</style>

<div class="client-dashboard">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p>Manage your vehicle maintenance and services</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3>My Vehicles</h3>
                    <div class="stat-number">0</div>
                </div>
                <i class="fas fa-car stat-icon"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3>Appointments</h3>
                    <div class="stat-number">0</div>
                </div>
                <i class="fas fa-calendar stat-icon"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <h3>Total Spent</h3>
                    <div class="stat-number">$0.00</div>
                </div>
                <i class="fas fa-credit-card stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="actions-section">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
            <a href="/vehicare_db/index.php#appointment" class="action-btn primary">
                <i class="fas fa-plus-circle"></i>
                <span>Book Appointment</span>
            </a>
            <a href="#" class="action-btn secondary">
                <i class="fas fa-car"></i>
                <span>My Vehicles</span>
            </a>
            <a href="#" class="action-btn secondary">
                <i class="fas fa-history"></i>
                <span>Service History</span>
            </a>
            <a href="/vehicare_db/client/profile.php" class="action-btn secondary">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="info-section">
        <h2 class="section-title">Getting Started</h2>
        <div class="info-grid">
            <div class="info-card">
                <i class="fas fa-car-alt"></i>
                <h3>Register Vehicle</h3>
                <p>Add your vehicles to your account and keep track of all maintenance records.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Schedule Service</h3>
                <p>Book maintenance appointments at your convenience with our service centers.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-file-alt"></i>
                <h3>Track Records</h3>
                <p>View all your service history and maintenance records in one place.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
