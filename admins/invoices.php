<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all invoices
$invoices = $conn->query("
    SELECT i.*, u.full_name, s.service_name, a.appointment_date, a.client_id
    FROM invoices i
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    LEFT JOIN users u ON a.client_id = u.user_id
    LEFT JOIN services s ON a.service_id = s.service_id
    ORDER BY i.invoice_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices & Billing - VehiCare Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal-color: #0ea5a4;
            --teal-dark: #0b7f7f;
            --primary: #d4794a;
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
        <a href="/vehicare_db/admins/assignments.php"><i class="fas fa-tasks"></i>Assignments</a>
        <a href="/vehicare_db/admins/queue.php"><i class="fas fa-list-ol"></i>Queue</a>
        <a href="/vehicare_db/admins/inventory.php"><i class="fas fa-boxes"></i>Inventory</a>
        <a href="/vehicare_db/admins/payments.php"><i class="fas fa-credit-card"></i>Payments</a>
        <a href="/vehicare_db/admins/invoices.php" class="active"><i class="fas fa-receipt"></i>Invoices</a>
        <a href="/vehicare_db/admins/ratings.php"><i class="fas fa-star"></i>Ratings</a>
        <a href="/vehicare_db/admins/notifications.php"><i class="fas fa-bell"></i>Notifications</a>
        <a href="/vehicare_db/admins/audit_logs.php"><i class="fas fa-history"></i>Audit Logs</a>
        <a href="/vehicare_db/logout.php" style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </aside>
    
    <main class="main-content">
        <div class="page-header">
            <h1>Invoices & Billing</h1>
        </div>
        
        <div class="data-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Client</th>
                        <th>Service</th>
                        <th>Appointment Date</th>
                        <th>Labor Cost</th>
                        <th>Parts Cost</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoices && $invoices->num_rows > 0): ?>
                        <?php while ($invoice = $invoices->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $invoice['invoice_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($invoice['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($invoice['service_name'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($invoice['appointment_date'] ?? now())); ?></td>
                            <td>₱<?php echo number_format($invoice['total_labor'] ?? 0, 2); ?></td>
                            <td>₱<?php echo number_format($invoice['total_parts'] ?? 0, 2); ?></td>
                            <td><strong>₱<?php echo number_format($invoice['grand_total'] ?? 0, 2); ?></strong></td>
                            <td>
                                <span class="badge bg-<?php echo ($invoice['payment_status'] ?? 'unpaid') == 'paid' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($invoice['payment_status'] ?? 'unpaid'); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary"><i class="fas fa-print"></i> Print</button>
                                <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i> View</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No invoices found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
