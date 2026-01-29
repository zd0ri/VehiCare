<?php


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
    <link href="https:
    <link rel="stylesheet" href="https:
    <link href="https:
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: 
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, 
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            padding: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar-brand {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand i {
            font-size: 32px;
            color: 
            display: block;
            margin-bottom: 10px;
        }

        .sidebar-brand h5 {
            color: 
            font-weight: 700;
            margin: 0;
            font-size: 16px;
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
            color: 
            border-left-color: 
        }

        .sidebar-menu i {
            font-size: 18px;
            width: 20px;
        }

        
        .main-content {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        
        .admin-header {
            background: 
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid 
        }

        .header-left h2 {
            margin: 0;
            color: 
            font-weight: 700;
            font-size: 24px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-time {
            font-size: 14px;
            color: 
            font-weight: 500;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: 
            border-radius: 20px;
            cursor: pointer;
        }

        .header-user i {
            width: 30px;
            height: 30px;
            background: 
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

        
        .admin-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid 
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid 
        }

        .card-header h3 {
            margin: 0;
            color: 
            font-weight: 700;
            font-size: 18px;
        }

        .card-header-link {
            color: 
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead {
            background: 
            border-bottom: 2px solid 
        }

        .admin-table thead th {
            color: 
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid 
            font-size: 13px;
            color: 
        }

        .admin-table tbody tr:hover {
            background: 
        }

        
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: absolute;
            }

            .main-content {
                margin-left: 0;
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .content-area {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-car"></i>
                <h5>VehiCare</h5>
            </div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                
                <!-- Booking & Service Management -->
                <li style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; font-weight: 600;">Bookings & Services</li>
                <li><a href="/vehicare_db/admins/appointments_bookings.php"><i class="fas fa-calendar-check"></i> Appointments & Bookings</a></li>
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="/vehicare_db/admins/queue_notifications.php"><i class="fas fa-hourglass"></i> Queue Management</a></li>
                
                <!-- Customer Management -->
                <li style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; font-weight: 600;">Customer Management</li>
                <li><a href="/vehicare_db/admins/customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i> Vehicle Information</a></li>
                
                <!-- Financial Management -->
                <li style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; font-weight: 600;">Financial</li>
                <li><a href="/vehicare_db/admins/payment_management.php"><i class="fas fa-credit-card"></i> Payment System</a></li>
                <li><a href="/vehicare_db/admins/billing_invoices.php"><i class="fas fa-file-invoice-dollar"></i> Billing & Invoices</a></li>
                
                <!-- Inventory & Staff -->
                <li style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; font-weight: 600;">Operations</li>
                <li><a href="/vehicare_db/admins/inventory_management.php"><i class="fas fa-warehouse"></i> Inventory Management</a></li>
                <li><a href="/vehicare_db/admins/staff.php"><i class="fas fa-people-group"></i> Staff Management</a></li>
                <li><a href="/vehicare_db/admins/staff_ratings_reports.php"><i class="fas fa-star"></i> Staff Ratings & Reports</a></li>
                
                <!-- System Management -->
                <li style="margin-top: 20px; padding: 10px 20px; color: rgba(255,255,255,0.5); font-size: 11px; text-transform: uppercase; font-weight: 600;">System</li>
                <li><a href="/vehicare_db/admins/users.php"><i class="fas fa-user-shield"></i> Users</a></li>
                <li><a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i> Audit Logs</a></li>
                
                <li style="margin-top: 20px;"><a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <h2><i class="<?php echo $page_icon; ?>"></i> <?php echo htmlspecialchars($page_title); ?></h2>
                </div>
                <div class="header-right">
                    <span class="header-time" id="current-time">09:55</span>
                    <div class="header-user">
                        <i class="fas fa-bell"></i>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content-area">

