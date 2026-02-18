<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

// Build query conditions
$conditions = ["i.client_id = $client_id"];
$params = [];
$types = "";

if ($status_filter !== 'all') {
    // Try payment_status first, fall back to status
    try {
        $test_query = $conn->query("SELECT payment_status FROM invoices LIMIT 1");
        $conditions[] = "i.payment_status = ?";
    } catch (Exception $e) {
        $conditions[] = "i.status = ?";
    }
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_filter === 'current_month') {
    $conditions[] = "MONTH(i.invoice_date) = MONTH(CURRENT_DATE()) AND YEAR(i.invoice_date) = YEAR(CURRENT_DATE())";
} elseif ($date_filter === 'last_month') {
    $conditions[] = "MONTH(i.invoice_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(i.invoice_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
} elseif ($date_filter === 'last_3_months') {
    $conditions[] = "i.invoice_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)";
}

$where_clause = implode(' AND ', $conditions);

// Get invoices with pagination
$page = $_GET['page'] ?? 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Try query with invoice_id in payments table first
try {
    $invoices_query = "
        SELECT i.*, a.appointment_date, a.appointment_time,
               s.service_name, v.plate_number, v.car_brand, v.car_model,
               SUM(p.amount) as total_paid
        FROM invoices i
        LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        LEFT JOIN payments p ON i.invoice_id = p.invoice_id
        WHERE $where_clause
        GROUP BY i.invoice_id
        ORDER BY i.invoice_date DESC
        LIMIT $per_page OFFSET $offset
    ";

    if (!empty($params)) {
        $stmt = $conn->prepare($invoices_query);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
        $stmt->execute();
        $invoices = $stmt->get_result();
    } else {
        $invoices = $conn->query($invoices_query);
    }
} catch (Exception $e) {
    // Fallback query without payments table or with different payment structure
    $invoices_query = "
        SELECT i.*, a.appointment_date, a.appointment_time,
               s.service_name, v.plate_number, v.car_brand, v.car_model,
               0 as total_paid
        FROM invoices i
        LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
        LEFT JOIN services s ON a.service_id = s.service_id
        LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        WHERE $where_clause
        ORDER BY i.invoice_date DESC
        LIMIT $per_page OFFSET $offset
    ";

    if (!empty($params)) {
        $stmt = $conn->prepare($invoices_query);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
        $stmt->execute();
        $invoices = $stmt->get_result();
    } else {
        $invoices = $conn->query($invoices_query);
    }
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM invoices i WHERE $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    call_user_func_array([$count_stmt, 'bind_param'], array_merge([$types], $params));
    $count_stmt->execute();
    $total_invoices = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_invoices = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_invoices / $per_page);

// Get summary statistics with error handling
$stats = [];
$stats['total_invoices'] = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;

try {
    $stats['unpaid_amount'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices WHERE client_id = $client_id AND payment_status = 'unpaid'")->fetch_assoc()['total'] ?? 0;
    $stats['paid_amount'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices WHERE client_id = $client_id AND payment_status = 'paid'")->fetch_assoc()['total'] ?? 0;
    $stats['pending_amount'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices WHERE client_id = $client_id AND payment_status = 'partial'")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    // Fallback without payment_status column
    $stats['unpaid_amount'] = $conn->query("SELECT SUM(grand_total) as total FROM invoices WHERE client_id = $client_id")->fetch_assoc()['total'] ?? 0;
    $stats['paid_amount'] = 0;
    $stats['pending_amount'] = 0;
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices & Bills - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .invoices-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #e17055 0%, #d63031 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient);
        }

        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5em;
            color: white;
            background: var(--gradient);
        }

        .stat-card .stat-value {
            font-size: 2em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .stat-card:nth-child(1) {
            --gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        }

        .stat-card:nth-child(2) {
            --gradient: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        .stat-card:nth-child(3) {
            --gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .stat-card:nth-child(4) {
            --gradient: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .invoice-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .invoice-card:hover {
            transform: translateY(-2px);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .invoice-number {
            font-size: 1.2em;
            font-weight: 700;
            color: #2c3e50;
        }

        .invoice-date {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-paid { background: #d4edda; color: #155724; }
        .status-unpaid { background: #f8d7da; color: #721c24; }
        .status-partial { background: #fff3cd; color: #856404; }
        .status-overdue { background: #f1c0c7; color: #842029; }

        .invoice-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
            color: white;
        }

        .detail-content h6 {
            margin: 0;
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-content p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .invoice-amount {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .amount-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .amount-value {
            font-size: 1.3em;
            font-weight: 700;
            color: #e74c3c;
        }

        .invoice-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 8px 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border: none;
        }

        .no-invoices {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-invoices i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .invoice-details {
                grid-template-columns: 1fr;
            }

            .invoice-actions {
                justify-content: stretch;
            }

            .invoice-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="invoices-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar me-3"></i>Invoices & Bills</h1>
            <p class="mb-0">Manage your service invoices and payment history</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_invoices']; ?></div>
                <div class="stat-label">Total Invoices</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['unpaid_amount'], 2); ?></div>
                <div class="stat-label">Outstanding Balance</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['paid_amount'], 2); ?></div>
                <div class="stat-label">Total Paid</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['pending_amount'], 2); ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Payment Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="unpaid" <?php echo $status_filter === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="partial" <?php echo $status_filter === 'partial' ? 'selected' : ''; ?>>Partially Paid</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select name="date" class="form-select">
                        <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Time</option>
                        <option value="current_month" <?php echo $date_filter === 'current_month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="last_month" <?php echo $date_filter === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                        <option value="last_3_months" <?php echo $date_filter === 'last_3_months' ? 'selected' : ''; ?>>Last 3 Months</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>

                <div class="col-md-3">
                    <a href="/vehicare_db/client/payments.php" class="btn btn-outline-success">
                        <i class="fas fa-credit-card me-2"></i>Payment History
                    </a>
                </div>
            </form>
        </div>

        <!-- Invoices List -->
        <?php if ($invoices && $invoices->num_rows > 0): ?>
            <?php while ($invoice = $invoices->fetch_assoc()): ?>
                <div class="invoice-card">
                    <div class="invoice-header">
                        <div>
                            <div class="invoice-number">Invoice #<?php echo str_pad($invoice['invoice_id'], 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="invoice-date"><?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></div>
                        </div>
                        <span class="status-badge status-<?php echo $invoice['payment_status'] ?? 'unpaid'; ?>">
                            <?php echo ucfirst($invoice['payment_status'] ?? 'unpaid'); ?>
                        </span>
                    </div>

                    <div class="invoice-details">
                        <?php if ($invoice['service_name']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Service</h6>
                                <p><?php echo htmlspecialchars($invoice['service_name']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($invoice['plate_number']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #e17055 0%, #d63031 100%);">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Vehicle</h6>
                                <p><?php echo htmlspecialchars($invoice['plate_number']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($invoice['appointment_date']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Service Date</h6>
                                <p><?php echo date('M j, Y', strtotime($invoice['appointment_date'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Invoice Date</h6>
                                <p><?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-amount">
                        <span class="amount-label">Total Amount:</span>
                        <span class="amount-value">₱<?php echo number_format($invoice['grand_total'], 2); ?></span>
                    </div>

                    <?php if ($invoice['total_paid'] && $invoice['total_paid'] > 0): ?>
                    <div class="invoice-amount" style="background: #d4edda;">
                        <span class="amount-label" style="color: #155724;">Amount Paid:</span>
                        <span class="amount-value" style="color: #155724;">₱<?php echo number_format($invoice['total_paid'], 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="invoice-actions">
                        <a href="/vehicare_db/client/invoice-details.php?id=<?php echo $invoice['invoice_id']; ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                        
                        <a href="/vehicare_db/client/download-invoice.php?id=<?php echo $invoice['invoice_id']; ?>" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-download me-1"></i>Download PDF
                        </a>
                        
                        <?php if (($invoice['payment_status'] ?? 'unpaid') !== 'paid'): ?>
                            <a href="/vehicare_db/client/make-payment.php?invoice=<?php echo $invoice['invoice_id']; ?>" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-credit-card me-1"></i>Pay Now
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === (int)$page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <!-- No Invoices -->
            <div class="no-invoices">
                <i class="fas fa-file-invoice"></i>
                <h3>No Invoices Found</h3>
                <p>You don't have any invoices matching your current filters.</p>
                <a href="/vehicare_db/client/book-appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Book a Service
                </a>
            </div>
        <?php endif; ?>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>