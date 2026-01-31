<?php
/**
 * Admin Layout Template
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #dc143c 0%, #dc143c 100%);
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
            color: #fff;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar-brand h5 {
            color: #fff;
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
            color: #fff;
            border-left-color: #a01030;
        }

        .sidebar-menu i {
            font-size: 18px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .admin-header {
            background: #fff;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .header-left h2 {
            margin: 0;
            color: #333;
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
            color: #666;
            font-weight: 500;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f0f0f0;
            border-radius: 20px;
            cursor: pointer;
        }

        .header-user i {
            width: 30px;
            height: 30px;
            background: #dc143c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Card Styles */
        .admin-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            font-weight: 700;
            font-size: 18px;
        }

        .card-header-link {
            color: #dc143c;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        /* Table Styles */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead {
            background: #f5f7fa;
            border-bottom: 2px solid #e0e0e0;
        }

        .admin-table thead th {
            color: #666;
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
            color: #333;
        }

        .admin-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* Responsive */
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
                <li><a href="/vehicare_db/admins/users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="/vehicare_db/admins/clients.php"><i class="fas fa-user-tie"></i> Clients</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i> Vehicles</a></li>
                <li><a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i> Appointments</a></li>
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="/vehicare_db/admins/staff.php"><i class="fas fa-people-group"></i> Staff</a></li>
                <li><a href="/vehicare_db/admins/parts.php"><i class="fas fa-box"></i> Parts</a></li>
                <li><a href="/vehicare_db/admins/payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
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
