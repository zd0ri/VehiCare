<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

// Get comprehensive statistics
$stats = [
    'total_clients' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client' AND status = 'active'")->fetch_assoc()['count'],
    'total_appointments' => $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'],
    'pending_appointments' => $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status IN ('pending', 'confirmed')")->fetch_assoc()['count'],
    'completed_appointments' => $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'completed'")->fetch_assoc()['count'],
    'total_vehicles' => $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'],
    'total_services' => $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'],
    'total_staff' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff' AND status = 'active'")->fetch_assoc()['count'],
    'total_parts' => $conn->query("SELECT COUNT(*) as count FROM parts")->fetch_assoc()['count'],
    'queue_pending' => $conn->query("SELECT COUNT(*) as count FROM queue_management WHERE status IN ('waiting', 'in-service')")->fetch_assoc()['count'],
    'unpaid_invoices' => $conn->query("SELECT COUNT(*) as count FROM invoices WHERE status NOT IN ('paid', 'cancelled')")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT COALESCE(SUM(grand_total), 0) as total FROM invoices WHERE status = 'paid'")->fetch_assoc()['total'],
    'pending_payments' => $conn->query("SELECT COUNT(*) as count FROM payments WHERE payment_status = 'pending'")->fetch_assoc()['count'],
    'unread_notifications' => $conn->query("SELECT COUNT(*) as count FROM notifications WHERE is_read = 0")->fetch_assoc()['count'],
];

// Get recent appointments
$recent_appointments = $conn->query("
    SELECT a.*, u.full_name, s.service_name 
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    JOIN services s ON a.service_id = s.service_id
    ORDER BY a.created_at DESC LIMIT 5
");

// Get recent payments
$recent_payments = $conn->query("
    SELECT p.*, u.full_name, i.invoice_number
    FROM payments p
    JOIN invoices i ON p.invoice_id = i.invoice_id
    JOIN users u ON i.user_id = u.user_id
    ORDER BY p.payment_date DESC LIMIT 5
");

// Get queue status
$queue_status = $conn->query("
    SELECT * FROM queue_management 
    WHERE status IN ('waiting', 'in-service')
    ORDER BY queue_position ASC LIMIT 5
");

// Get low inventory
$low_inventory = $conn->query("
    SELECT * FROM parts 
    WHERE quantity <= 10 
    ORDER BY quantity ASC LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
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
            width: 250px;
            background: linear-gradient(180deg, #1a3a52 0%, #2d5a7b 100%);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            padding: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand i {
            font-size: 32px;
            color: #4ECDC4;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 18px;
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
            gap: 12px;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #4ECDC4;
            border-left-color: #e74c3c;
        }

        .sidebar-menu i {
            font-size: 18px;
            width: 20px;
        }

        .sidebar-section-title {
            padding: 20px 20px 10px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e74c3c;
        }

        .top-navbar h2 {
            margin: 0;
            color: #1a3a52;
            font-weight: 700;
            font-size: 24px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-time {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 20px;
            cursor: pointer;
        }

        .navbar-user i {
            width: 30px;
            height: 30px;
            background: #1a3a52;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #e74c3c;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .stat-label {
            font-size: 12px;
            color: #999;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a3a52;
            margin-bottom: 5px;
        }

        .stat-change {
            font-size: 12px;
            color: #28a745;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Module Cards */
        .module-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            padding: 25px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border-left: 4px solid #e74c3c;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .module-icon {
            font-size: 32px;
            color: #2d5a7b;
        }

        .module-title {
            font-weight: 700;
            font-size: 16px;
            color: #1a3a52;
        }

        .module-description {
            font-size: 13px;
            color: #999;
            margin: 0;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .data-table thead th {
            background: #f8f9fa;
            padding: 12px 15px;
            font-size: 12px;
            font-weight: 600;
            color: #1a3a52;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table tbody td {
            padding: 14px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            color: #666;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a3a52;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #e74c3c;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 30px;
        }

        .quick-action-btn {
            background: linear-gradient(135deg, #2d5a7b 0%, #1a3a52 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(45, 90, 123, 0.3);
            color: white;
            text-decoration: none;
        }

        .quick-action-btn i {
            font-size: 20px;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #e74c3c;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-car"></i>
                <h5>VehiCare</h5>
            </div>

            <div class="sidebar-section-title">Main</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            </ul>

            <div class="sidebar-section-title">Services & Bookings</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i> Appointments</a></li>
                <li><a href="/vehicare_db/admins/manage_appointments.php"><i class="fas fa-tasks"></i> Manage Bookings</a></li>
            </ul>

            <div class="sidebar-section-title">Operations</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/queue_management.php"><i class="fas fa-hourglass-start"></i> Queue Management</a></li>
                <li><a href="/vehicare_db/admins/queue_notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="/vehicare_db/admins/staff.php"><i class="fas fa-people-group"></i> Staff/Technicians</a></li>
            </ul>

            <div class="sidebar-section-title">Management</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/customers.php"><i class="fas fa-users"></i> Clients</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i> Vehicles</a></li>
                <li><a href="/vehicare_db/admins/parts.php"><i class="fas fa-box"></i> Inventory</a></li>
            </ul>

            <div class="sidebar-section-title">Financial</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
                <li><a href="/vehicare_db/admins/billing_invoices.php"><i class="fas fa-file-invoice"></i> Invoices</a></li>
                <li><a href="/vehicare_db/admins/payment_management.php"><i class="fas fa-money-bill"></i> Payment Mgmt</a></li>
            </ul>

            <div class="sidebar-section-title">Reports & Analytics</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/staff_ratings_reports.php"><i class="fas fa-star"></i> Staff Ratings</a></li>
                <li><a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i> Audit Logs</a></li>
            </ul>

            <div class="sidebar-section-title">Account</div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <h2>Dashboard</h2>
                <div class="navbar-right">
                    <span class="navbar-time" id="current-time">09:55</span>
                    <div class="navbar-user">
                        <i class="fas fa-user"></i>
                        <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="/vehicare_db/admins/manage_appointments.php?action=new" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        New Booking
                    </a>
                    <a href="/vehicare_db/admins/queue_management.php" class="quick-action-btn">
                        <i class="fas fa-hourglass-start"></i>
                        Queue
                    </a>
                    <a href="/vehicare_db/admins/payments.php" class="quick-action-btn">
                        <i class="fas fa-credit-card"></i>
                        Payments
                    </a>
                    <a href="/vehicare_db/admins/parts.php" class="quick-action-btn">
                        <i class="fas fa-box"></i>
                        Inventory
                    </a>
                    <a href="/vehicare_db/admins/customers.php" class="quick-action-btn">
                        <i class="fas fa-users"></i>
                        Clients
                    </a>
                    <a href="/vehicare_db/admins/staff.php" class="quick-action-btn">
                        <i class="fas fa-people-group"></i>
                        Staff
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card" style="border-top-color: #1a3a52;">
                        <div class="stat-label">Total Clients</div>
                        <div class="stat-value"><?php echo $stats['total_clients']; ?></div>
                        <div class="stat-change"><i class="fas fa-arrow-up"></i> Active Users</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #2d5a7b;">
                        <div class="stat-label">Appointments</div>
                        <div class="stat-value"><?php echo $stats['pending_appointments']; ?></div>
                        <div class="stat-change"><?php echo $stats['pending_appointments']; ?> Pending</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #e74c3c;">
                        <div class="stat-label">Queue Waiting</div>
                        <div class="stat-value"><?php echo $stats['queue_pending']; ?></div>
                        <div class="stat-change">In Queue</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #4ECDC4;">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">₱<?php echo number_format($stats['total_revenue'], 0); ?></div>
                        <div class="stat-change">From Paid Invoices</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #28a745;">
                        <div class="stat-label">Total Staff</div>
                        <div class="stat-value"><?php echo $stats['total_staff']; ?></div>
                        <div class="stat-change">Active Technicians</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #ffc107;">
                        <div class="stat-label">Unpaid Invoices</div>
                        <div class="stat-value"><?php echo $stats['unpaid_invoices']; ?></div>
                        <div class="stat-change">Pending Payment</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #dc3545;">
                        <div class="stat-label">Low Inventory</div>
                        <div class="stat-value"><?php echo $stats['total_parts']; ?></div>
                        <div class="stat-change">Total Parts</div>
                    </div>
                    <div class="stat-card" style="border-top-color: #17a2b8;">
                        <div class="stat-label">Notifications</div>
                        <div class="stat-value"><?php echo $stats['unread_notifications']; ?></div>
                        <div class="stat-change">Unread</div>
                    </div>
                </div>

                <!-- Main Dashboard Grid -->
                <div class="dashboard-grid">
                    <!-- Left Column -->
                    <div>
                        <!-- Recent Appointments -->
                        <div style="background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="section-title">
                                <i class="fas fa-calendar-check"></i>
                                Recent Appointments
                            </div>
                            <?php if ($recent_appointments && $recent_appointments->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($apt = $recent_appointments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($apt['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['service_name']); ?></td>
                                        <td>
                                            <span class="badge-<?php echo $apt['status'] == 'completed' ? 'success' : 'info'; ?>">
                                                <?php echo ucfirst($apt['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p style="text-align: center; color: #999; padding: 20px;">No recent appointments</p>
                            <?php endif; ?>
                        </div>

                        <!-- Queue Status -->
                        <div style="background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="section-title">
                                <i class="fas fa-hourglass-start"></i>
                                Queue Status
                            </div>
                            <?php if ($queue_status && $queue_status->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Queue #</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($queue = $queue_status->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo $queue['queue_position']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($queue['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($queue['service_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge-<?php echo $queue['status'] == 'in-service' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst(str_replace('-', ' ', $queue['status'])); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p style="text-align: center; color: #999; padding: 20px;">No items in queue</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <!-- Recent Payments -->
                        <div style="background: white; border-radius: 10px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="section-title">
                                <i class="fas fa-credit-card"></i>
                                Recent Payments
                            </div>
                            <?php if ($recent_payments && $recent_payments->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($payment = $recent_payments->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['full_name']); ?></td>
                                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge-<?php echo $payment['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($payment['payment_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p style="text-align: center; color: #999; padding: 20px;">No recent payments</p>
                            <?php endif; ?>
                        </div>

                        <!-- Low Inventory -->
                        <div style="background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <div class="section-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                Low Inventory Alert
                            </div>
                            <?php if ($low_inventory && $low_inventory->num_rows > 0): ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Part</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($part = $low_inventory->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($part['part_name']); ?></td>
                                        <td>
                                            <span class="badge-danger">
                                                <?php echo $part['quantity']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <?php else: ?>
                            <p style="text-align: center; color: #999; padding: 20px;">All inventory levels good</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- System Modules Grid -->
                <h3 style="margin-top: 40px; margin-bottom: 20px; color: #1a3a52; font-weight: 700;">System Modules</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <a href="/vehicare_db/admins/services.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-cogs"></i></div>
                        <div class="module-title">Services</div>
                        <p class="module-description">Manage service catalog</p>
                    </a>
                    <a href="/vehicare_db/admins/customers.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-users"></i></div>
                        <div class="module-title">Clients</div>
                        <p class="module-description">Client management system</p>
                    </a>
                    <a href="/vehicare_db/admins/staff.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-people-group"></i></div>
                        <div class="module-title">Technicians</div>
                        <p class="module-description">Assign & manage staff</p>
                    </a>
                    <a href="/vehicare_db/admins/vehicles.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-car"></i></div>
                        <div class="module-title">Vehicles</div>
                        <p class="module-description">Client vehicle database</p>
                    </a>
                    <a href="/vehicare_db/admins/manage_appointments.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-calendar-check"></i></div>
                        <div class="module-title">Bookings</div>
                        <p class="module-description">Appointment & walk-in system</p>
                    </a>
                    <a href="/vehicare_db/admins/queue_management.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-hourglass-start"></i></div>
                        <div class="module-title">Queue</div>
                        <p class="module-description">Queue management</p>
                    </a>
                    <a href="/vehicare_db/admins/parts.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-box"></i></div>
                        <div class="module-title">Inventory</div>
                        <p class="module-description">Parts & inventory CRUD</p>
                    </a>
                    <a href="/vehicare_db/admins/payments.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-credit-card"></i></div>
                        <div class="module-title">Payments</div>
                        <p class="module-description">Payment management</p>
                    </a>
                    <a href="/vehicare_db/admins/billing_invoices.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-file-invoice"></i></div>
                        <div class="module-title">Invoices</div>
                        <p class="module-description">Billing system</p>
                    </a>
                    <a href="/vehicare_db/admins/staff_ratings_reports.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-star"></i></div>
                        <div class="module-title">Ratings</div>
                        <p class="module-description">Staff ratings & reports</p>
                    </a>
                    <a href="/vehicare_db/admins/queue_notifications.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-bell"></i></div>
                        <div class="module-title">Notifications</div>
                        <p class="module-description">Notification system</p>
                    </a>
                    <a href="/vehicare_db/admins/audit_logs.php" class="module-card">
                        <div class="module-icon"><i class="fas fa-history"></i></div>
                        <div class="module-title">Audit Logs</div>
                        <p class="module-description">System audit trail</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}`;
        }
        updateTime();
        setInterval(updateTime, 60000);
    </script>
</body>
</html>
