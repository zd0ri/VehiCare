<?php
/**
 * Admin Layout Header Template
 * Include this file to use consistent admin layout across all pages
 * 
 * Usage:
 * 1. Define $page_title before including
 * 2. Define $page_icon before including (optional)
 * 3. Include this file
 * 4. Your content goes here
 * 5. Include admin_layout_footer.php
 */

if (!isset($page_title)) {
    $page_title = 'Admin Panel';
}

if (!isset($page_icon)) {
    $page_icon = 'fas fa-dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - VehiCare</title>
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
        
        .sidebar-menu .menu-section {
            padding: 10px 20px 5px;
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 5px;
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
            border-left-color: rgba(255,255,255,0.8);
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
        
        /* Content Cards */
        .content-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }
        
        .content-card h3 {
            color: var(--teal-dark);
            margin-bottom: 20px;
            font-weight: 600;
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
        
        /* Buttons */
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-primary:hover {
            background: var(--secondary);
            border-color: var(--secondary);
        }
        
        .btn-outline-primary {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
        }
        
        /* Form Controls */
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(220, 20, 60, 0.25);
        }
        
        /* Alerts */
        .alert-success {
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            border-left: 4px solid #17a2b8;
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
                text-align: center;
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
                <small>Admin Panel</small>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-dashboard"></i>Dashboard</a></li>
                
                <li class="menu-section">BOOKINGS</li>
                <li><a href="/vehicare_db/admins/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>"><i class="fas fa-calendar"></i>Appointments</a></li>
                <li><a href="/vehicare_db/admins/walk_in_booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'walk_in_booking.php' ? 'active' : ''; ?>"><i class="fas fa-door-open"></i>Walk-In Bookings</a></li>
                
                <li class="menu-section">MANAGEMENT</li>
                <li><a href="/vehicare_db/admins/clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i>Clients</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vehicles.php' ? 'active' : ''; ?>"><i class="fas fa-car"></i>Vehicles</a></li>
                <li><a href="/vehicare_db/admins/technicians.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'technicians.php' ? 'active' : ''; ?>"><i class="fas fa-tools"></i>Technicians</a></li>
                <li><a href="/vehicare_db/admins/assignments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i>Assignments</a></li>
                
                <li class="menu-section">OPERATIONS</li>
                <li><a href="/vehicare_db/admins/queue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'queue.php' ? 'active' : ''; ?>"><i class="fas fa-list-ol"></i>Queue</a></li>
                <li><a href="/vehicare_db/admins/inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>"><i class="fas fa-boxes"></i>Inventory</a></li>
                <li><a href="/vehicare_db/admins/services.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>"><i class="fas fa-wrench"></i>Services</a></li>
                
                <li class="menu-section">FINANCIAL</li>
                <li><a href="/vehicare_db/admins/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i>Payments</a></li>
                <li><a href="/vehicare_db/admins/invoices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>"><i class="fas fa-receipt"></i>Invoices</a></li>
                
                <li class="menu-section">REPORTS & SYSTEM</li>
                <li><a href="/vehicare_db/admins/ratings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ratings.php' ? 'active' : ''; ?>"><i class="fas fa-star"></i>Ratings</a></li>
                <li><a href="/vehicare_db/admins/notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>"><i class="fas fa-bell"></i>Notifications</a></li>
                <li><a href="/vehicare_db/admins/audit_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'audit_logs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i>Audit Logs</a></li>
                
                <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;">
                    <a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1><i class="<?php echo $page_icon; ?> me-2"></i><?php echo htmlspecialchars($page_title); ?></h1>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION['email'] ?? 'Administrator'); ?></span>
                    <a href="/vehicare_db/logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
            <!-- Page content goes after this point -->
