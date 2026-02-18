<?php
/**
 * Admin Layout Header
 * Main layout template for admin dashboard pages
 */

// Ensure user is authenticated and has admin access
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../middleware/Auth.php';
require_once __DIR__ . '/../../middleware/RBAC.php';

$auth = new Auth($pdo);
$rbac = new RBAC($pdo);

// Check if user is logged in
if (!$auth->isAuthenticated()) {
    header('Location: /vehicare_db/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();

// Check if user has admin access (admin or staff roles)
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'staff'])) {
    header('Location: /vehicare_db/client/dashboard.php');
    exit;
}

// Get page title from $page_title variable or use default
$page_title = $page_title ?? 'Dashboard';
$page_icon = $page_icon ?? 'fas fa-tachometer-alt';
$breadcrumbs = $breadcrumbs ?? [];

// CSRF Token for forms
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title><?php echo htmlspecialchars($page_title); ?> - VehiCare Admin</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/vehicare_db/assets/images/favicon.ico">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Main Admin Stylesheet -->
    <link rel="stylesheet" href="/vehicare_db/assets/css/admin.css">
    
    <!-- Additional page-specific styles -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <i class="fas fa-car-side"></i>
                </div>
                <h1 class="brand-name">VehiCare</h1>
                <p class="brand-tagline">Admin Dashboard</p>
            </div>

            <!-- Navigation Menu -->
            <ul class="sidebar-menu">
                <!-- Dashboard -->
                <li><a href="/vehicare_db/admins/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Dashboard</span>
                </a></li>

                <!-- Appointments Section -->
                <li class="menu-section">Appointments</li>
                <li><a href="/vehicare_db/admins/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-check"></i>
                    <span class="menu-text">All Appointments</span>
                </a></li>
                <li><a href="/vehicare_db/admins/queue.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'queue.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span class="menu-text">Service Queue</span>
                </a></li>
                <li><a href="/vehicare_db/admins/walk_in_booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'walk_in_booking.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus"></i>
                    <span class="menu-text">Walk-in Booking</span>
                </a></li>
                <li><a href="/vehicare_db/admins/assignments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'assignments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i>
                    <span class="menu-text">Tech Assignments</span>
                </a></li>

                <!-- Services Section -->
                <li class="menu-section">Services</li>
                <li><a href="/vehicare_db/admins/services.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'services.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tools"></i>
                    <span class="menu-text">Service Types</span>
                </a></li>
                <li><a href="/vehicare_db/admins/inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'inventory.php' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i>
                    <span class="menu-text">Parts & Inventory</span>
                </a></li>
                <li><a href="/vehicare_db/admins/parts.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'parts.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span class="menu-text">Parts Management</span>
                </a></li>

                <!-- Customers & Vehicles -->
                <li class="menu-section">Customers</li>
                <li><a href="/vehicare_db/admins/clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'clients.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">All Clients</span>
                </a></li>
                <li><a href="/vehicare_db/admins/vehicles.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'vehicles.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span class="menu-text">Vehicles</span>
                </a></li>

                <!-- Financial -->
                <li class="menu-section">Financial</li>
                <li><a href="/vehicare_db/admins/invoices.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'invoices.php' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span class="menu-text">Invoices</span>
                </a></li>
                <li><a href="/vehicare_db/admins/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span class="menu-text">Payments</span>
                </a></li>

                <?php if ($rbac->hasPermission($_SESSION['user_role'], 'admin.full_access')): ?>
                <!-- Admin Only Section -->
                <li class="menu-section">Administration</li>
                <li><a href="/vehicare_db/admins/staff.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'staff.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i>
                    <span class="menu-text">Staff Management</span>
                </a></li>
                <li><a href="/vehicare_db/admins/technicians.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'technicians.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hard-hat"></i>
                    <span class="menu-text">Technicians</span>
                </a></li>
                <li><a href="/vehicare_db/admins/users.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users-cog"></i>
                    <span class="menu-text">User Management</span>
                </a></li>
                <li><a href="/vehicare_db/admins/audit_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'audit_logs.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="menu-text">Audit Logs</span>
                </a></li>
                <?php endif; ?>

                <!-- Reports & Analytics -->
                <li class="menu-section">Reports</li>
                <li><a href="/vehicare_db/admins/ratings.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'ratings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-star"></i>
                    <span class="menu-text">Reviews & Ratings</span>
                </a></li>
                <li><a href="/vehicare_db/admins/notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'notifications.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bell"></i>
                    <span class="menu-text">Notifications</span>
                </a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-title">
                    <?php if (!empty($breadcrumbs)): ?>
                        <nav class="breadcrumb">
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <?php if (isset($crumb['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['name']); ?></a>
                                <?php else: ?>
                                    <span><?php echo htmlspecialchars($crumb['name']); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </nav>
                    <?php endif; ?>
                    <i class="page-icon <?php echo htmlspecialchars($page_icon); ?>"></i>
                    <h1><?php echo htmlspecialchars($page_title); ?></h1>
                </div>

                <div class="header-actions">
                    <!-- Global Search -->
                    <div class="search-container">
                        <input type="text" id="globalSearch" placeholder="Search..." class="form-control">
                        <div id="globalSearchResults" class="search-results"></div>
                    </div>

                    <!-- Notifications -->
                    <button class="notification-btn" id="notificationBtn" data-tooltip="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationBadge">0</span>
                    </button>

                    <!-- User Profile -->
                    <a href="/vehicare_db/profile.php" class="user-info">
                        <div class="user-avatar">
                            <?php 
                            $name_parts = explode(' ', $current_user['full_name']);
                            $initials = substr($name_parts[0], 0, 1) . (isset($name_parts[1]) ? substr($name_parts[1], 0, 1) : '');
                            echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
                            <div class="user-role"><?php echo htmlspecialchars(ucfirst($current_user['role'])); ?></div>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </a>

                    <!-- Mobile Sidebar Toggle -->
                    <button class="sidebar-toggle d-md-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Logout -->
                    <a href="/vehicare_db/logout.php" class="btn btn-outline-secondary" data-tooltip="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>

            <!-- Notification Panel -->
            <div id="notificationPanel" class="notification-panel">
                <div class="notification-header">
                    <h3>Notifications</h3>
                    <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">Mark All Read</button>
                </div>
                <div class="notification-list" id="notificationList">
                    <!-- Notifications loaded via JavaScript -->
                </div>
            </div>

            <!-- Page Content -->
            <main class="content-area">
                <?php
                // Display flash messages if any
                if (isset($_SESSION['flash_message'])) {
                    $flash = $_SESSION['flash_message'];
                    echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . '">';
                    echo '<i class="fas fa-' . ($flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle') . '"></i>';
                    echo htmlspecialchars($flash['message']);
                    echo '</div>';
                    unset($_SESSION['flash_message']);
                }
                ?>

                <!-- Page content will be inserted here -->