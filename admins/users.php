<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all users
$users_query = "SELECT user_id, username, email, full_name, phone, role, status, created_date, last_login FROM users ORDER BY created_date DESC";
$users_result = $conn->query($users_query);
$users = [];

if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Count users by role
$admin_count = 0;
$staff_count = 0;
$client_count = 0;

foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        $admin_count++;
    } elseif ($user['role'] === 'staff') {
        $staff_count++;
    } else {
        $client_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - VehiCare</title>
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
            background: linear-gradient(180deg, #0052cc 0%, #0052cc 100%);
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
            border-left-color: #ff6b6b;
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
            background: #0052cc;
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

        /* Stats Grid */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-top: 4px solid #ff6b6b;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .stat-card.admin {
            border-top-color: #dc3545;
        }

        .stat-card.staff {
            border-top-color: #0052cc;
        }

        .stat-card.client {
            border-top-color: #27ae60;
        }

        .stat-card i {
            font-size: 28px;
            margin-bottom: 10px;
            display: block;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        .filter-section .row {
            gap: 15px;
        }

        .filter-section input,
        .filter-section select {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .filter-section input:focus,
        .filter-section select:focus {
            border-color: #0052cc;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1);
        }

        /* Table Styles */
        .table-responsive {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .dashboard-table thead {
            background: #f5f7fa;
            border-bottom: 2px solid #e0e0e0;
        }

        .dashboard-table thead th {
            color: #666;
            font-weight: 600;
            padding: 14px 16px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dashboard-table tbody td {
            padding: 14px 16px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
            color: #333;
        }

        .dashboard-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0052cc 0%, #0088ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .user-name {
            display: block;
            font-weight: 600;
            color: #333;
        }

        .user-email {
            font-size: 12px;
            color: #999;
        }

        /* Status Badge */
        .badge {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 4px;
            display: inline-block;
        }

        .badge.active {
            background: #d4edda;
            color: #155724;
        }

        .badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .badge.suspended {
            background: #fff3cd;
            color: #856404;
        }

        .badge.admin {
            background: #f8d7da;
            color: #721c24;
        }

        .badge.staff {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge.client {
            background: #d4edda;
            color: #155724;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-info {
            background: #e7f3ff;
            color: #0052cc;
        }

        .btn-info:hover {
            background: #0052cc;
            color: white;
        }

        .btn-warning {
            background: #fff3cd;
            color: #856404;
        }

        .btn-warning:hover {
            background: #856404;
            color: white;
        }

        .btn-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-danger:hover {
            background: #721c24;
            color: white;
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

            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .filter-section .row {
                grid-template-columns: 1fr !important;
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
                <li><a href="/vehicare_db/admins/users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
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
                    <h2><i class="fas fa-users"></i> User Management</h2>
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
                <!-- Statistics -->
                <div class="stats-container">
                    <div class="stat-card admin">
                        <i class="fas fa-shield-alt" style="color: #dc3545;"></i>
                        <div class="stat-number"><?php echo $admin_count; ?></div>
                        <div class="stat-label">Administrators</div>
                    </div>
                    <div class="stat-card staff">
                        <i class="fas fa-wrench" style="color: #0052cc;"></i>
                        <div class="stat-number"><?php echo $staff_count; ?></div>
                        <div class="stat-label">Staff Members</div>
                    </div>
                    <div class="stat-card client">
                        <i class="fas fa-user" style="color: #27ae60;"></i>
                        <div class="stat-number"><?php echo $client_count; ?></div>
                        <div class="stat-label">Clients</div>
                    </div>
                </div>

                <!-- Filter and Search -->
                <div class="filter-section">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by username, email, or name...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterRole">
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="staff">Staff</option>
                                <option value="client">Client</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table class="dashboard-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Member Since</th>
                                <th>Last Login</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="user-row" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                        <div>
                                            <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                            <span class="user-email">@<?php echo htmlspecialchars($user['username']); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_date'])); ?></td>
                                <td>
                                    <?php 
                                    if ($user['last_login']) {
                                        echo date('M d, Y H:i', strtotime($user['last_login']));
                                    } else {
                                        echo '<span style="color: #999;">Never</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-sm btn-info" title="View"><i class="fas fa-eye"></i></button>
                                        <button class="btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></button>
                                        <button class="btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($users)): ?>
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px; margin-top: 20px;">
                    <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; display: block; margin-bottom: 15px;"></i>
                    <p style="color: #999; font-size: 14px;">No users found in the system.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}`;
        }
        updateTime();
        setInterval(updateTime, 60000);

        // Search and filter
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterRole').addEventListener('change', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);

        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('filterRole').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const role = row.dataset.role;
                const status = row.dataset.status;

                const matchesSearch = text.includes(searchInput);
                const matchesRole = !roleFilter || role === roleFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
            });
        }
    </script>
</body>
</html>
            letter-spacing: 1px;
        }

        .role-badge {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .role-badge.admin {
            background: #f8d7da;
            color: #721c24;
        }

        .role-badge.staff {
            background: #d1ecf1;
            color: #0c5460;
        }

        .role-badge.client {
            background: #d4edda;
            color: #155724;
        }

        .status-badge {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 20px;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .status-badge.suspended {
            background: #fff3cd;
            color: #856404;
        }

        .table-responsive {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #1a3a52 0%, #2d5a7b 100%);
            color: white;
        }

        .table tbody tr {
            border-bottom: 1px solid #e0e0e0;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/adminHeader.php'; ?>

    <div class="container-fluid mt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-2"><i class="fas fa-users"></i> User Management</h1>
                <p class="text-muted">Monitor all admins, staff, and client accounts</p>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card admin">
                <i class="fas fa-shield-alt fa-2x" style="color: #dc3545; margin-bottom: 10px;"></i>
                <div class="stat-number"><?php echo $admin_count; ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            <div class="stat-card staff">
                <i class="fas fa-wrench fa-2x" style="color: #0d6efd; margin-bottom: 10px;"></i>
                <div class="stat-number"><?php echo $staff_count; ?></div>
                <div class="stat-label">Staff Members</div>
            </div>
            <div class="stat-card client">
                <i class="fas fa-user fa-2x" style="color: #198754; margin-bottom: 10px;"></i>
                <div class="stat-number"><?php echo $client_count; ?></div>
                <div class="stat-label">Clients</div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by username, email, or name...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterRole">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                        <option value="client">Client</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Member Since</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr class="user-row" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                        <td>
                            <div class="user-info">
                                <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></div>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                    <br>
                                    <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="role-badge <?php echo $user['role']; ?>">
                                <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'shield-alt' : ($user['role'] === 'staff' ? 'wrench' : 'user'); ?>"></i>
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['status']; ?>">
                                <i class="fas fa-<?php echo $user['status'] === 'active' ? 'check-circle' : ($user['status'] === 'suspended' ? 'exclamation-circle' : 'times-circle'); ?>"></i>
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_date'])); ?></td>
                        <td>
                            <?php 
                            if ($user['last_login']) {
                                echo date('M d, Y H:i', strtotime($user['last_login']));
                            } else {
                                echo '<span class="text-muted">Never</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($users)): ?>
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle"></i> No users found in the system.
        </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('filterRole').addEventListener('change', filterTable);
        document.getElementById('filterStatus').addEventListener('change', filterTable);

        function filterTable() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const roleFilter = document.getElementById('filterRole').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const rows = document.querySelectorAll('.user-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const role = row.dataset.role;
                const status = row.dataset.status;

                const matchesSearch = text.includes(searchInput);
                const matchesRole = !roleFilter || role === roleFilter;
                const matchesStatus = !statusFilter || status === statusFilter;

                row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
            });
        }
    </script>
</body>
</html>
