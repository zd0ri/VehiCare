<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$ratings = $conn->query("
    SELECT r.*, s.staff_name, u.full_name as client_name
    FROM ratings r
    JOIN staff s ON r.staff_id = s.staff_id
    JOIN users u ON r.client_id = u.user_id
    ORDER BY r.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ratings & Reports - VehiCare Admin</title>
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
        
        .rating-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .stars {
            color: #dc143c;
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
        <a href="/vehicare_db/admins/assignments.php"><i class="fas fa-tasks"></i>Assignments</a>
        <a href="/vehicare_db/admins/queue.php"><i class="fas fa-list-ol"></i>Queue</a>
        <a href="/vehicare_db/admins/inventory.php"><i class="fas fa-boxes"></i>Inventory</a>
        <a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i>Payments</a>
        <a href="/vehicare_db/admins/invoices.php"><i class="fas fa-receipt"></i>Invoices</a>
        <a href="/vehicare_db/admins/ratings.php" class="active"><i class="fas fa-star"></i>Ratings</a>
        <a href="/vehicare_db/admins/notifications.php"><i class="fas fa-bell"></i>Notifications</a>
        <a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i>Audit Logs</a>
        <a href="/vehicare_db/logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Ratings & Reports</h1>
        </div>
        
        <?php if ($ratings && $ratings->num_rows > 0): ?>
            <?php while ($rating = $ratings->fetch_assoc()): ?>
            <div class="rating-card">
                <div class="row">
                    <div class="col-md-8">
                        <h6><?php echo htmlspecialchars($rating['first_name'] . ' ' . $rating['last_name']); ?></h6>
                        <p class="text-muted mb-2">Rated by: <?php echo htmlspecialchars($rating['client_name']); ?></p>
                        <p><?php echo htmlspecialchars($rating['feedback']); ?></p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="stars" style="font-size: 1.5rem; margin-bottom: 10px;">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $rating['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        <p><strong><?php echo $rating['rating']; ?>/5</strong></p>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($rating['rating_date'])); ?></small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No ratings found.</div>
        <?php endif; ?>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
