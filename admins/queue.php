<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$queues = $conn->query("
    SELECT q.*, a.appointment_date, a.appointment_time, u.full_name
    FROM queue q
    JOIN appointments a ON q.appointment_id = a.appointment_id
    JOIN users u ON a.client_id = u.user_id
    ORDER BY q.queue_number ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Management - VehiCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
        
        .queue-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 4px solid var(--teal-color);
        }
        
        .queue-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--teal-dark);
            width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Sidebar (same as before) -->
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
        <a href="/vehicare_db/admins/assignments.php"><i class="fas fa-tasks"></i>Assignments</a>
        <a href="/vehicare_db/admins/queue.php" class="active"><i class="fas fa-list-ol"></i>Queue</a>
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
            <h1>Queue Management</h1>
        </div>
        
        <?php if ($queues && $queues->num_rows > 0): ?>
            <?php while ($queue = $queues->fetch_assoc()): ?>
            <div class="queue-card">
                <div class="d-flex align-items-center">
                    <div class="queue-number">Q<?php echo str_pad($queue['queue_number'], 3, '0', STR_PAD_LEFT); ?></div>
                    <div class="flex-grow-1 ms-4">
                        <h5><?php echo htmlspecialchars($queue['full_name']); ?></h5>
                        <p class="text-muted mb-0">
                            Appointment: <?php echo date('M d, Y H:i', strtotime($queue['appointment_date'] . ' ' . $queue['appointment_time'])); ?>
                        </p>
                    </div>
                    <div>
                        <span class="badge bg-<?php echo $queue['status'] == 'pending' ? 'warning' : 'success'; ?>">
                            <?php echo ucfirst($queue['status']); ?>
                        </span>
                        <button class="btn btn-sm btn-success ms-2"><i class="fas fa-check"></i> Complete</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No queues at the moment.</div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
