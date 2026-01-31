<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all technicians from users table where role = 'staff'
$technicians = $conn->query("SELECT * FROM users WHERE role = 'staff' ORDER BY user_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Management - VehiCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-red: #dc143c;
            --dark-red: #a01030;
            --black: #1a1a1a;
            --white: #ffffff;
            --light-gray: #f5f7fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light-gray);
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--dark-red) 0%, var(--primary-red) 100%);
            color: var(--white);
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
        }

        .sidebar-header small {
            opacity: 0.9;
            font-size: 12px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu.section-label {
            padding: 15px 20px 5px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 0.7;
            margin-top: 10px;
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-left-color: var(--white);
        }
        
        .sidebar i {
            width: 20px;
            margin-right: 12px;
            font-size: 16px;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
        }
        
        .page-header {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            margin: 0;
            color: var(--primary-red);
            font-weight: 700;
            font-size: 28px;
        }

        .btn-add {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--dark-red) 100%);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 20, 60, 0.3);
            color: var(--white);
        }
        
        .tech-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
            border: 1px solid #e8e8e8;
        }
        
        .tech-card:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .tech-card h5 {
            color: var(--primary-red);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .tech-card .info {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 10px 0;
        }

        .status-active {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-inactive {
            background: #ffebee;
            color: #c62828;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-sm-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: var(--white);
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }

        .alert-info {
            background: #e3f2fd;
            color: #0d47a1;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--dark-red) 100%);
            color: var(--white);
            border: none;
        }

        .modal-title {
            font-weight: 700;
        }

        .btn-close-white {
            filter: brightness(0) invert(1);
        }

        .form-label {
            font-weight: 600;
            color: var(--black);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            padding: 10px 12px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 3px rgba(220, 20, 60, 0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: absolute;
                z-index: 999;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-car"></i> VehiCare</h3>
                <small>Admin Panel</small>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/index.php"><i class="fas fa-dashboard"></i> Dashboard</a></li>
                
                <li class="sidebar-menu section-label">Bookings</li>
                <li><a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i> Appointments</a></li>
                <li><a href="/vehicare_db/admins/walk_in_booking.php"><i class="fas fa-door-open"></i> Walk-In Bookings</a></li>
                
                <li class="sidebar-menu section-label">Management</li>
                <li><a href="/vehicare_db/admins/clients.php"><i class="fas fa-users"></i> Clients</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i> Vehicles</a></li>
                <li><a href="/vehicare_db/admins/technicians.php" class="active"><i class="fas fa-tools"></i> Technicians</a></li>
                <li><a href="/vehicare_db/admins/assignments.php"><i class="fas fa-tasks"></i> Assignments</a></li>
                
                <li class="sidebar-menu section-label">Operations</li>
                <li><a href="/vehicare_db/admins/queue.php"><i class="fas fa-list-ol"></i> Queue</a></li>
                <li><a href="/vehicare_db/admins/inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-wrench"></i> Services</a></li>
                
                <li class="sidebar-menu section-label">Financial</li>
                <li><a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
                <li><a href="/vehicare_db/admins/invoices.php"><i class="fas fa-receipt"></i> Invoices</a></li>
                
                <li class="sidebar-menu section-label">Reports</li>
                <li><a href="/vehicare_db/admins/ratings.php"><i class="fas fa-star"></i> Ratings</a></li>
                <li><a href="/vehicare_db/admins/notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                <li><a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i> Audit Logs</a></li>
                
                <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;">
                    <a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-tools"></i> Technician Management</h1>
                <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="fas fa-plus"></i> Add Technician
                </button>
            </div>
            
            <?php if ($technicians && $technicians->num_rows > 0): ?>
                <?php while ($tech = $technicians->fetch_assoc()): ?>
                <div class="tech-card">
                    <div class="row">
                        <div class="col-md-9">
                            <h5><?php echo htmlspecialchars($tech['full_name']); ?></h5>
                            <div class="info">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($tech['email']); ?>
                            </div>
                            <div class="info">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($tech['phone'] ?? 'N/A'); ?>
                            </div>
                            <div class="info">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php 
                                    $location = [];
                                    if (!empty($tech['city'])) $location[] = $tech['city'];
                                    if (!empty($tech['state'])) $location[] = $tech['state'];
                                    echo htmlspecialchars(implode(', ', $location) ?: 'N/A'); 
                                ?>
                            </div>
                            <div class="status-badge <?php echo $tech['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <i class="fas fa-<?php echo $tech['status'] === 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                                <?php echo ucfirst($tech['status']); ?>
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="action-buttons">
                                <button class="btn-sm-action btn-edit" title="Edit Technician"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn-sm-action btn-delete" title="Delete Technician"><i class="fas fa-trash"></i> Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h4>No Technicians Found</h4>
                    <p class="text-muted">No technicians have been added yet. Get started by adding your first technician.</p>
                    <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Add First Technician
                    </button>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Add Technician Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Technician</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, var(--primary-red) 0%, var(--dark-red) 100%); border: none; color: white;">Add Technician</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
