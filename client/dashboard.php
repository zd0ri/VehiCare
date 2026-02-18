<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Get client information
$client_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$client_query->bind_param("i", $client_id);
$client_query->execute();
$client = $client_query->get_result()->fetch_assoc();

// Get quick stats for dashboard
$stats = [];

// Count appointments
$stats['total_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;
$stats['pending_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE client_id = $client_id AND status = 'pending'")->fetch_assoc()['count'] ?? 0;
$stats['upcoming_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE client_id = $client_id AND appointment_date >= CURDATE() AND status IN ('pending', 'confirmed')")->fetch_assoc()['count'] ?? 0;

// Count vehicles
try {
    $stats['total_vehicles'] = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE client_id = $client_id AND status = 'active'")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    // If status column doesn't exist, just count all vehicles for this client
    try {
        $stats['total_vehicles'] = $conn->query("SELECT COUNT(*) as count FROM vehicles WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;
    } catch (Exception $e2) {
        $stats['total_vehicles'] = 0;
    }
}

// Count unpaid invoices - handle missing payment_status column
try {
    $stats['unpaid_invoices'] = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE client_id = $client_id AND payment_status = 'unpaid'")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    // If payment_status column doesn't exist, set to 0
    $stats['unpaid_invoices'] = 0;
}

// Count unread notifications - handle missing table
try {
    $stats['unread_notifications'] = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE client_id = $client_id AND is_read = FALSE")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    $stats['unread_notifications'] = 0; // Table might not exist yet
}

// Get recent appointments with error handling
try {
    $recent_appointments = $conn->query("
        SELECT a.*, s.service_name, v.plate_number, v.car_brand, v.car_model
        FROM appointments a
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        WHERE a.client_id = $client_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 5
    ");
} catch (Exception $e) {
    $recent_appointments = false; // Handle missing tables/columns
}

// Get recent notifications
try {
    $recent_notifications = $conn->query("
        SELECT * FROM notifications 
        WHERE client_id = $client_id
        ORDER BY created_at DESC 
        LIMIT 5
    ");
} catch (Exception $e) {
    $recent_notifications = null; // Table might not exist yet
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .client-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            color: #fff;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 25px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-header .client-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2em;
            font-weight: bold;
            color: #fff;
        }

        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2em;
            font-weight: 600;
        }

        .sidebar-header p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 0.9em;
        }

        .sidebar-nav {
            padding: 20px 15px;
        }

        .nav-section {
            margin-bottom: 30px;
        }

        .nav-section-title {
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 15px;
            padding: 0 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            transform: translateX(5px);
        }

        .nav-link i {
            width: 20px;
            margin-right: 15px;
            font-size: 1.1em;
        }

        .nav-link .badge {
            background: #dc3545;
            color: white;
            border-radius: 20px;
            padding: 2px 8px;
            font-size: 0.75em;
            margin-left: auto;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }

        .welcome-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .welcome-header h1 {
            font-size: 2.2em;
            margin: 0 0 10px 0;
            font-weight: 700;
            font-size: 1.1em;
        }

        /* Quick Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            color: white;
            margin-bottom: 15px;
        }

        .stat-card .stat-value {
            font-size: 2em;
            font-weight: 700;
            margin: 10px 0 5px;
            color: #2c3e50;
        }

        .stat-card .stat-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-header h3 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .section-header .btn {
            margin-left: auto;
        }

        /* Tables */
        .table {
            margin: 0;
        }

        .table th {
            border: none;
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            padding: 15px;
        }

        .table td {
            border: none;
            border-bottom: 1px solid #eee;
            padding: 15px;
            vertical-align: middle;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="client-container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="client-avatar">
                    <?php 
                    $initials = substr($client['full_name'], 0, 1);
                    if (strpos($client['full_name'], ' ') !== false) {
                        $names = explode(' ', $client['full_name']);
                        $initials = substr($names[0], 0, 1) . substr(end($names), 0, 1);
                    }
                    echo strtoupper($initials);
                    ?>
                </div>
                <h4><?php echo htmlspecialchars($client['full_name']); ?></h4>
                <p>Client Account</p>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="/vehicare_db/client/dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Appointments</div>
                    <a href="/vehicare_db/client/appointments.php" class="nav-link">
                        <i class="fas fa-calendar-check"></i>
                        My Appointments
                        <?php if ($stats['pending_appointments'] > 0): ?>
                            <span class="badge"><?php echo $stats['pending_appointments']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/vehicare_db/client/book-appointment.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        Book New Service
                    </a>
                    <a href="/vehicare_db/client/appointment-history.php" class="nav-link">
                        <i class="fas fa-history"></i>
                        Service History
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Vehicles</div>
                    <a href="/vehicare_db/client/vehicles.php" class="nav-link">
                        <i class="fas fa-car"></i>
                        My Vehicles
                        <span class="badge"><?php echo $stats['total_vehicles']; ?></span>
                    </a>
                    <a href="/vehicare_db/client/add-vehicle.php" class="nav-link">
                        <i class="fas fa-plus"></i>
                        Add Vehicle
                    </a>
                    <a href="/vehicare_db/client/maintenance-reminders.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Maintenance Reminders
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Billing</div>
                    <a href="/vehicare_db/client/invoices.php" class="nav-link">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Invoices & Bills
                        <?php if ($stats['unpaid_invoices'] > 0): ?>
                            <span class="badge"><?php echo $stats['unpaid_invoices']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/vehicare_db/client/payments.php" class="nav-link">
                        <i class="fas fa-credit-card"></i>
                        Payment History
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="/vehicare_db/client/profile.php" class="nav-link">
                        <i class="fas fa-user-edit"></i>
                        Profile Settings
                    </a>
                    <a href="/vehicare_db/client/notifications.php" class="nav-link">
                        <i class="fas fa-bell"></i>
                        Notifications
                        <?php if ($stats['unread_notifications'] > 0): ?>
                            <span class="badge"><?php echo $stats['unread_notifications']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/vehicare_db/client/reviews.php" class="nav-link">
                        <i class="fas fa-star"></i>
                        Reviews & Ratings
                    </a>
                    <a href="/vehicare_db/client/preferences.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Preferences
                    </a>
                    <a href="/vehicare_db/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Header -->
            <div class="welcome-header">
                <h1>Welcome back, <?php echo explode(' ', $client['full_name'])[0]; ?>!</h1>
                <p>Manage your vehicles, appointments, and service history from your personal dashboard.</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['upcoming_appointments']; ?></div>
                    <div class="stat-label">Upcoming Appointments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['total_vehicles']; ?></div>
                    <div class="stat-label">Registered Vehicles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['unpaid_invoices']; ?></div>
                    <div class="stat-label">Pending Payments</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['unread_notifications']; ?></div>
                    <div class="stat-label">New Notifications</div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-calendar-alt me-2"></i>Recent Appointments</h3>
                    <a href="/vehicare_db/client/appointments.php" class="btn btn-outline-primary">View All</a>
                </div>
                
                <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Service</th>
                                <th>Vehicle</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appointment = $recent_appointments->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                    <small class="text-muted"><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($appointment['plate_number']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($appointment['car_brand'] . ' ' . $appointment['car_model']); ?></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                        <?php echo ucfirst($appointment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/vehicare_db/client/appointment-details.php?id=<?php echo $appointment['appointment_id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No appointments found.</p>
                    <a href="/vehicare_db/client/book-appointment.php" class="btn btn-primary">Book Your First Service</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="content-section">
                <div class="section-header">
                    <h3><i class="fas fa-rocket me-2"></i>Quick Actions</h3>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="/vehicare_db/client/book-appointment.php" class="btn btn-primary w-100 py-3">
                            <i class="fas fa-plus-circle mb-2 d-block"></i>
                            Book Service
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/vehicare_db/client/add-vehicle.php" class="btn btn-success w-100 py-3">
                            <i class="fas fa-car mb-2 d-block"></i>
                            Add Vehicle
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/vehicare_db/client/invoices.php" class="btn btn-info w-100 py-3">
                            <i class="fas fa-file-invoice-dollar mb-2 d-block"></i>
                            View Bills
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="/vehicare_db/client/reviews.php" class="btn btn-warning w-100 py-3">
                            <i class="fas fa-star mb-2 d-block"></i>
                            Rate Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
