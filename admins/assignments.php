<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$assignments = $conn->query("
    SELECT a.*, u.full_name as client_name, s.full_name as staff_name, st.service_name
    FROM assignments a
    JOIN appointments ap ON a.appointment_id = ap.appointment_id
    JOIN users u ON ap.client_id = u.user_id
    JOIN staff s ON a.staff_id = s.staff_id
    JOIN services st ON ap.service_id = st.service_id
    ORDER BY a.assigned_date DESC
");

$page_title = "Assignments";
$page_icon = "fas fa-tasks";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Assignment Management</h3>
        <button class="btn btn-primary" onclick="alert('Add Assignment feature coming soon')">
            <i class="fas fa-plus"></i> New Assignment
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Staff Member</th>
                    <th>Service</th>
                    <th>Assigned Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($assignments && $assignments->num_rows > 0): ?>
                    <?php while($assignment = $assignments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $assignment['assignment_id']; ?></td>
                        <td><?php echo htmlspecialchars($assignment['client_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($assignment['staff_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($assignment['service_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($assignment['assigned_date'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editAssignment(<?php echo $assignment['assignment_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAssignment(<?php echo $assignment['assignment_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No assignments found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editAssignment(id) {
    alert('Edit assignment ' + id);
}

function deleteAssignment(id) {
    if (confirm('Are you sure you want to delete this assignment?')) {
        alert('Delete assignment ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Assignments - VehiCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --teal-color: #dc143c;
            --teal-dark: #a01030;
            --primary: #dc143c;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal-color) 100%);
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
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
            color: #fff;
            border-left-color: var(--primary);
        }
        
        .sidebar i {
            width: 24px;
            margin-right: 12px;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            width: calc(100% - 280px);
        }
        
        .page-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .page-header h1 {
            margin: 0;
            color: var(--teal-dark);
            font-weight: 700;
        }
        
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
            <h3 style="margin: 0;"><i class="fas fa-car me-2"></i>VehiCare</h3>
            <small>Admin Panel</small>
        </div>
        
        <a href="/vehicare_db/admins/index.php"><i class="fas fa-dashboard"></i>Dashboard</a>
        <a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i>Appointments</a>
        <a href="/vehicare_db/admins/walk_in_booking.php"><i class="fas fa-door-open"></i>Walk-In</a>
        <a href="/vehicare_db/admins/clients.php"><i class="fas fa-users"></i>Clients</a>
        <a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i>Vehicles</a>
        <a href="/vehicare_db/admins/technicians.php"><i class="fas fa-tools"></i>Technicians</a>
        <a href="/vehicare_db/admins/assignments.php" class="active"><i class="fas fa-tasks"></i>Assignments</a>
        <a href="/vehicare_db/admins/queue.php"><i class="fas fa-list-ol"></i>Queue</a>
        <a href="/vehicare_db/admins/inventory.php"><i class="fas fa-boxes"></i>Inventory</a>
        <a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i>Payments</a>
        <a href="/vehicare_db/admins/invoices.php"><i class="fas fa-receipt"></i>Invoices</a>
        <a href="/vehicare_db/admins/ratings.php"><i class="fas fa-star"></i>Ratings</a>
        <a href="/vehicare_db/admins/notifications.php"><i class="fas fa-bell"></i>Notifications</a>
        <a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i>Audit Logs</a>
        <a href="/vehicare_db/logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Technician Assignments</h1>
        </div>
        
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Technician</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Assigned Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($assignments && $assignments->num_rows > 0): ?>
                        <?php while ($assign = $assignments->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($assign['first_name'] . ' ' . $assign['staff_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($assign['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($assign['service_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($assign['assigned_date'])); ?></td>
                            <td><span class="badge bg-warning">Assigned</span></td>
                            <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No assignments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
