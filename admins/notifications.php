<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Handle sending notifications
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notif'])) {
    $user_id = intval($_POST['user_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $type = $conn->real_escape_string($_POST['type']);
    
    $conn->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES ($user_id, '$title', '$message', '$type', NOW())");
    $_SESSION['success'] = "Notification sent successfully!";
    header("Location: /vehicare_db/admins/notifications.php");
    exit;
}

$notifications = $conn->query("SELECT n.*, u.full_name FROM notifications n JOIN users u ON n.user_id = u.user_id ORDER BY n.created_at DESC LIMIT 50");
$clients = $conn->query("SELECT user_id, full_name FROM users WHERE role='client'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - VehiCare Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-header h1 {
            margin: 0;
            color: var(--teal-dark);
            font-weight: 700;
        }
        
        .notif-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border-left: 4px solid var(--teal-color);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal-color) 100%);
            color: #fff;
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
        <a href="/vehicare_db/admins/ratings.php"><i class="fas fa-star"></i>Ratings</a>
        <a href="/vehicare_db/admins/notifications.php" class="active"><i class="fas fa-bell"></i>Notifications</a>
        <a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i>Audit Logs</a>
        <a href="/vehicare_db/logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Notifications</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendModal">
                <i class="fas fa-paper-plane"></i> Send Notification
            </button>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <h5 class="mb-3">Recent Notifications</h5>
        
        <?php if ($notifications && $notifications->num_rows > 0): ?>
            <?php while ($notif = $notifications->fetch_assoc()): ?>
            <div class="notif-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6><?php echo htmlspecialchars($notif['title']); ?></h6>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                        <small class="text-muted">To: <?php echo htmlspecialchars($notif['full_name']); ?></small>
                    </div>
                    <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></small>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No notifications yet.</div>
        <?php endif; ?>
    </main>
    
    <!-- Send Modal -->
    <div class="modal fade" id="sendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Send Notification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="send_notif" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label">Recipient *</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">Select Client</option>
                                <?php $clients->data_seek(0); while ($client = $clients->fetch_assoc()): ?>
                                <option value="<?php echo $client['user_id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notification Type</label>
                            <select name="type" class="form-control">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="alert">Alert</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Message *</label>
                            <textarea name="message" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
