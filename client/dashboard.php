<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$db_error = null;



$vehicles_query = "SELECT COUNT(*) as count FROM vehicles WHERE user_id = $user_id";
$vehicles_result = $conn->query($vehicles_query);
$vehicles_count = 0;
if (!$vehicles_result) {
    $db_error = "Database error loading vehicle count: " . $conn->error;
} elseif ($vehicles_result && $vehicles_result->num_rows > 0) {
    $vehicles_count = $vehicles_result->fetch_assoc()['count'];
}


$appointments_query = "SELECT COUNT(*) as count FROM appointments WHERE user_id = $user_id AND status NOT IN ('cancelled', 'completed')";
$appointments_result = $conn->query($appointments_query);
$appointments_count = 0;
if (!$appointments_result) {
    $db_error = "Database error loading appointments: " . $conn->error;
} elseif ($appointments_result && $appointments_result->num_rows > 0) {
    $appointments_count = $appointments_result->fetch_assoc()['count'];
}


$spending_query = "SELECT SUM(service_cost) as total FROM service_history WHERE user_id = $user_id";
$spending_result = $conn->query($spending_query);
$total_spent = 0;
if (!$spending_result) {
    $db_error = "Database error loading spending: " . $conn->error;
} elseif ($spending_result && $spending_result->num_rows > 0) {
    $spending_data = $spending_result->fetch_assoc();
    $total_spent = $spending_data['total'] ?? 0;
}


$profile_complete = false;
$profile_query = "SELECT is_profile_complete FROM customer_profiles WHERE user_id = $user_id";
$profile_result = $conn->query($profile_query);
if ($profile_result && $profile_result->num_rows > 0) {
    $profile_data = $profile_result->fetch_assoc();
    $profile_complete = $profile_data['is_profile_complete'] == 1 ? true : false;
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Welcome Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p style="margin: 0; opacity: 0.9;">Manage your vehicle maintenance and services</p>
    </div>

    <!-- Database Error Alert -->
    <?php if ($db_error): ?>
    <div style="background: 
        <h5 style="margin: 0 0 10px 0;"><i class="fas fa-exclamation-triangle"></i> Database Issue Detected</h5>
        <p style="margin: 0 0 10px 0;">We encountered an issue loading your dashboard data. This is likely because the database needs to be initialized.</p>
        <p style="margin: 0; font-size: 0.9em; color: 
        <p style="margin: 10px 0 0 0;">
            <a href="/vehicare_db/fix_database.php" style="background: 
                <i class="fas fa-wrench"></i> Run Database Fix
            </a>
            <a href="/vehicare_db/setup.php" style="background: 
                <i class="fas fa-database"></i> Run Full Setup
            </a>
        </p>
    </div>
    <?php endif; ?>

    <!-- Profile Completion Alert -->
    <?php if (!$profile_complete && !$db_error): ?>
    <div style="background: 
        <h5 style="margin: 0 0 10px 0; color: 
        <p style="margin: 0 0 10px 0; color: 
        <a href="/vehicare_db/client/profile.php" style="background: 
            Complete Now
        </a>
    </div>
    <?php endif; ?>

    <!-- Dashboard Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- My Vehicles -->
        <a href="/vehicare_db/client/vehicles.php" style="text-decoration: none;">
            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="margin: 0; color: 
                        <h3 style="margin: 10px 0 0 0; color: 
                    </div>
                    <i class="fas fa-car" style="font-size: 40px; color: 
                </div>
            </div>
        </a>

        <!-- Appointments -->
        <a href="/vehicare_db/client/appointments.php" style="text-decoration: none;">
            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="margin: 0; color: 
                        <h3 style="margin: 10px 0 0 0; color: 
                    </div>
                    <i class="fas fa-calendar" style="font-size: 40px; color: 
                </div>
            </div>
        </a>

        <!-- Total Spent -->
        <a href="/vehicare_db/client/service_history.php" style="text-decoration: none;">
            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <p style="margin: 0; color: 
                        <h3 style="margin: 10px 0 0 0; color: 
                    </div>
                    <i class="fas fa-credit-card" style="font-size: 40px; color: 
                </div>
            </div>
        </a>
    </div>

    <!-- Main Features -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Profile Management -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-user-circle" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/profile.php" style="background: 
                <i class="fas fa-arrow-right"></i> Go to Profile
            </a>
        </div>

        <!-- Vehicle Management -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-car" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/vehicles.php" style="background: 
                <i class="fas fa-arrow-right"></i> Manage Vehicles
            </a>
        </div>

        <!-- Appointments -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-calendar" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/appointments.php" style="background: 
                <i class="fas fa-arrow-right"></i> View Appointments
            </a>
        </div>

        <!-- Queue Status -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-hourglass-start" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/queue_status.php" style="background: 
                <i class="fas fa-arrow-right"></i> Check Queue
            </a>
        </div>

        <!-- Service History -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-history" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/service_history.php" style="background: 
                <i class="fas fa-arrow-right"></i> View History
            </a>
        </div>

        <!-- Quick Book -->
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, 
                <i class="fas fa-plus" style="font-size: 40px;"></i>
            </div>
            <h5 style="margin: 0 0 10px 0; color: 
            <p style="margin: 0 0 15px 0; color: 
            <a href="/vehicare_db/client/book_appointment.php" style="background: 
                <i class="fas fa-arrow-right"></i> Book Service
            </a>
        </div>
    </div>

    <!-- Info Section -->
    <div style="background: 
        <h5 style="margin: 0 0 15px 0; color: 
        <p style="margin: 0 0 10px 0; color: 
            Welcome to VehiCare! Here's what you can do:
        </p>
        <ul style="margin: 0; padding-left: 20px; color: 
            <li style="margin: 5px 0;">Complete your profile to unlock all features</li>
            <li style="margin: 5px 0;">Add your vehicles for easy service booking</li>
            <li style="margin: 5px 0;">Schedule appointments or join the walk-in queue</li>
            <li style="margin: 5px 0;">Track your service history and expenses</li>
            <li style="margin: 5px 0;">Check your queue position and estimated wait time</li>
        </ul>
    </div>
</div>

<style>
    a[href] > div {
        transition: all 0.3s ease;
    }
    a[href] > div:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }
</style>

