<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get dashboard statistics
$stats = [];
$stats['total_clients'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client' AND status = 'active'")->fetch_assoc()['count'] ?? 0;
$stats['total_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status IN ('Pending', 'Confirmed')")->fetch_assoc()['count'] ?? 0;
$stats['pending_payments'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices")->fetch_assoc()['total'] ?? 0;
$stats['total_revenue'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices WHERE invoice_id IS NOT NULL")->fetch_assoc()['total'] ?? 0;
$stats['total_technicians'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff' AND status = 'active'")->fetch_assoc()['count'] ?? 0;
$stats['queue_pending'] = $conn->query("SELECT COUNT(*) as count FROM queue WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;

// Get recent appointments
$recent_appointments = $conn->query("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, 
           u.full_name, s.service_name
    FROM appointments a
    JOIN users u ON a.client_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    ORDER BY a.appointment_date DESC LIMIT 5
");

// Get recent notifications
$notifications = @$conn->query("
    SELECT * FROM audittrail 
    ORDER BY action_date DESC LIMIT 5
");
if (!$notifications) {
    $notifications = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-color: #dc143c;
            --teal-dark: #a01030;
            --primary: #dc143c;
            --secondary: #a01030;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal-color) 100%);
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-left-color: var(--secondary);
        }
        
        .sidebar-menu i {
            width: 24px;
            margin-right: 12px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
        }
        
        /* Top Header */
        .top-header {
            background: #fff;
            padding: 15px 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-header h1 {
            margin: 0;
            color: var(--teal-dark);
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        
        /* Stat Cards */
        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .stat-card.teal {
            border-left-color: var(--teal-color);
        }
        
        .stat-card.primary {
            border-left-color: var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--teal-dark);
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.95rem;
            color: #666;
            font-weight: 600;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: var(--secondary);
            opacity: 0.8;
        }
        
        /* Tables */
        .data-table {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .data-table thead {
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal-color) 100%);
            color: #fff;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .top-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-car me-2"></i>VehiCare</h3>
                <small>Admin Dashboard</small>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/index.php" class="active"><i class="fas fa-dashboard"></i>Dashboard</a></li>
                
                <li><a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i>Appointments</a></li>
                
                <li><a href="/vehicare_db/admins/walk_in_booking.php"><i class="fas fa-door-open"></i>Walk-In Bookings</a></li>
                
                <li><a href="/vehicare_db/admins/clients.php"><i class="fas fa-users"></i>Clients</a></li>
                
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i>Vehicles</a></li>
                
                <li><a href="/vehicare_db/admins/technicians.php"><i class="fas fa-tools"></i>Technicians</a></li>
                
                <li><a href="/vehicare_db/admins/assignments.php"><i class="fas fa-tasks"></i>Assignments</a></li>
                
                <li><a href="/vehicare_db/admins/queue.php"><i class="fas fa-list-ol"></i>Queue Management</a></li>
                
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-wrench"></i>Services</a></li>
                
                <li><a href="/vehicare_db/admins/inventory.php"><i class="fas fa-boxes"></i>Inventory</a></li>
                
                <li><a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i>Payments</a></li>
                
                <li><a href="/vehicare_db/admins/invoices.php"><i class="fas fa-receipt"></i>Invoices</a></li>
                
                <li><a href="/vehicare_db/admins/ratings.php"><i class="fas fa-star"></i>Ratings & Reports</a></li>
                
                <li><a href="/vehicare_db/admins/notifications.php"><i class="fas fa-bell"></i>Notifications</a></li>
                
                <li><a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i>Audit Logs</a></li>
                
                <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;">
                    <a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    <img src="https://via.placeholder.com/40" alt="User">
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_clients']; ?></div>
                                <div class="stat-label">Active Clients</div>
                            </div>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card teal">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_appointments']; ?></div>
                                <div class="stat-label">Pending Appointments</div>
                            </div>
                            <i class="fas fa-calendar stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card primary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number"><?php echo $stats['queue_pending']; ?></div>
                                <div class="stat-label">Queue Pending</div>
                            </div>
                            <i class="fas fa-list-ol stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number"><?php echo $stats['total_technicians']; ?></div>
                                <div class="stat-label">Active Technicians</div>
                            </div>
                            <i class="fas fa-tools stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card teal">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number">₱<?php echo number_format($stats['pending_payments'], 2); ?></div>
                                <div class="stat-label">Pending Payments</div>
                            </div>
                            <i class="fas fa-hourglass-half stat-icon"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="stat-card primary">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="stat-number">₱<?php echo number_format($stats['total_revenue'], 2); ?></div>
                                <div class="stat-label">Total Revenue</div>
                            </div>
                            <i class="fas fa-chart-line stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Data -->
            <div class="row mt-4">
                <div class="col-lg-6">
                    <div class="data-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Recent Appointments</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                                    <?php while ($apt = $recent_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($apt['full_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($apt['service_name']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                        <td><span class="badge bg-warning"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">No appointments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="data-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Recent Notifications</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($notifications && $notifications->num_rows > 0): ?>
                                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($notif['title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($notif['message'], 0, 50)); ?>...</small>
                                        </td>
                                        <td><small><?php echo date('M d, h:i A', strtotime($notif['created_at'])); ?></small></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">No notifications</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
