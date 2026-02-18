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
$method_filter = $_GET['method'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

// Build query conditions
$conditions = ["p.client_id = $client_id"];
$params = [];
$types = "";

if ($status_filter !== 'all') {
    // Try payment_status first, fall back to status
    try {
        $test_query = $conn->query("SELECT payment_status FROM payments LIMIT 1");
        $conditions[] = "p.payment_status = ?";
    } catch (Exception $e) {
        $conditions[] = "p.status = ?";
    }
    $params[] = $status_filter;
    $types .= "s";
}

if ($method_filter !== 'all') {
    $conditions[] = "p.payment_method = ?";
    $params[] = $method_filter;
    $types .= "s";
}

if ($date_filter === 'current_month') {
    $conditions[] = "MONTH(p.payment_date) = MONTH(CURRENT_DATE()) AND YEAR(p.payment_date) = YEAR(CURRENT_DATE())";
} elseif ($date_filter === 'last_month') {
    $conditions[] = "MONTH(p.payment_date) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(p.payment_date) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
} elseif ($date_filter === 'last_3_months') {
    $conditions[] = "p.payment_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)";
}

$where_clause = implode(' AND ', $conditions);

// Get payments with pagination
$page = $_GET['page'] ?? 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$payments_query = "
    SELECT p.*, i.invoice_id, i.invoice_date, i.grand_total as invoice_total,
           a.appointment_date, s.service_name, v.plate_number, v.car_brand, v.car_model
    FROM payments p
    LEFT JOIN invoices i ON p.invoice_id = i.invoice_id
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    WHERE $where_clause
    ORDER BY p.payment_date DESC
    LIMIT $per_page OFFSET $offset
";

if (!empty($params)) {
    $stmt = $conn->prepare($payments_query);
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    $stmt->execute();
    $payments = $stmt->get_result();
} else {
    $payments = $conn->query($payments_query);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM payments p WHERE $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    call_user_func_array([$count_stmt, 'bind_param'], array_merge([$types], $params));
    $count_stmt->execute();
    $total_payments = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_payments = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_payments / $per_page);

// Get summary statistics
$stats = [];
try {
    $stats['total_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id AND payment_status = 'completed'")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    try {
        $stats['total_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id AND status = 'completed'")->fetch_assoc()['count'] ?? 0;
    } catch (Exception $e2) {
        $stats['total_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;
    }
}

try {
    $stats['total_amount'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND payment_status = 'completed'")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    try {
        $stats['total_amount'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND status = 'completed'")->fetch_assoc()['total'] ?? 0;
    } catch (Exception $e2) {
        $stats['total_amount'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id")->fetch_assoc()['total'] ?? 0;
    }
}

try {
    $stats['this_month'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND payment_status = 'completed' AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    try {
        $stats['this_month'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND status = 'completed' AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;
    } catch (Exception $e2) {
        $stats['this_month'] = $conn->query("SELECT SUM(amount) as total FROM payments WHERE client_id = $client_id AND MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0;
    }
}

try {
    $stats['pending_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id AND payment_status = 'pending'")->fetch_assoc()['count'] ?? 0;
} catch (Exception $e) {
    try {
        $stats['pending_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id AND status = 'pending'")->fetch_assoc()['count'] ?? 0;
    } catch (Exception $e2) {
        $stats['pending_payments'] = $conn->query("SELECT COUNT(*) as count FROM payments WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - VehiCare</title>
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

        .payments-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
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
            --gradient: linear-gradient(135deg, #00b894 0%, #00a085 100%);
        }

        .stat-card:nth-child(2) {
            --gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        }

        .stat-card:nth-child(3) {
            --gradient: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
        }

        .stat-card:nth-child(4) {
            --gradient: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .payment-card:hover {
            transform: translateY(-2px);
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .payment-id {
            font-size: 1.1em;
            font-weight: 700;
            color: #2c3e50;
        }

        .payment-date {
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

        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #d1ecf1; color: #0c5460; }

        .payment-details {
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

        .payment-amount {
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
            font-size: 1.4em;
            font-weight: 700;
            color: #00b894;
        }

        .payment-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .method-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .method-credit-card { background: #e3f2fd; color: #1976d2; }
        .method-gcash { background: #e8f5e8; color: #2e7d32; }
        .method-paymaya { background: #fff3e0; color: #f57c00; }
        .method-cash { background: #f3e5f5; color: #7b1fa2; }
        .method-bank-transfer { background: #fce4ec; color: #c2185b; }

        .no-payments {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-payments i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 8px 20px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                text-align: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .payment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .payment-details {
                grid-template-columns: 1fr;
            }

            .payment-actions {
                justify-content: stretch;
            }

            .payment-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="payments-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-credit-card me-3"></i>Payment History</h1>
            <p class="mb-0">Track all your payments and transaction records</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value"><?php echo $stats['total_payments']; ?></div>
                <div class="stat-label">Total Payments</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['total_amount'], 2); ?></div>
                <div class="stat-label">Total Amount Paid</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-month"></i>
                </div>
                <div class="stat-value">₱<?php echo number_format($stats['this_month'], 2); ?></div>
                <div class="stat-label">This Month</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?php echo $stats['pending_payments']; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="row align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-select">
                        <option value="all" <?php echo $method_filter === 'all' ? 'selected' : ''; ?>>All Methods</option>
                        <option value="credit_card" <?php echo $method_filter === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="gcash" <?php echo $method_filter === 'gcash' ? 'selected' : ''; ?>>GCash</option>
                        <option value="paymaya" <?php echo $method_filter === 'paymaya' ? 'selected' : ''; ?>>PayMaya</option>
                        <option value="cash" <?php echo $method_filter === 'cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="bank_transfer" <?php echo $method_filter === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date</label>
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
                    <a href="/vehicare_db/client/invoices.php" class="btn btn-outline-primary">
                        <i class="fas fa-file-invoice me-2"></i>View Invoices
                    </a>
                </div>
            </form>
        </div>

        <!-- Payments List -->
        <?php if ($payments && $payments->num_rows > 0): ?>
            <?php while ($payment = $payments->fetch_assoc()): ?>
                <div class="payment-card">
                    <div class="payment-header">
                        <div>
                            <div class="payment-id">Payment #<?php echo str_pad($payment['payment_id'], 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="payment-date"><?php echo date('M j, Y g:i A', strtotime($payment['payment_date'])); ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-badge status-<?php echo $payment['payment_status'] ?? $payment['status'] ?? 'unknown'; ?>">
                                <?php echo ucfirst($payment['payment_status'] ?? $payment['status'] ?? 'unknown'); ?>
                            </span>
                            <span class="method-badge method-<?php echo str_replace(' ', '-', strtolower($payment['payment_method'])); ?>">
                                <?php echo str_replace('_', ' ', $payment['payment_method']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="payment-details">
                        <?php if ($payment['invoice_id']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Invoice</h6>
                                <p>#<?php echo str_pad($payment['invoice_id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($payment['service_name']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #e17055 0%, #d63031 100%);">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Service</h6>
                                <p><?php echo htmlspecialchars($payment['service_name']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment['plate_number']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Vehicle</h6>
                                <p><?php echo htmlspecialchars($payment['plate_number']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($payment['reference_number']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                                <i class="fas fa-hashtag"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Reference No.</h6>
                                <p><?php echo htmlspecialchars($payment['reference_number']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="payment-amount">
                        <span class="amount-label">Amount Paid:</span>
                        <span class="amount-value">₱<?php echo number_format($payment['amount'], 2); ?></span>
                    </div>

                    <?php if ($payment['notes']): ?>
                    <div class="payment-notes">
                        <small class="text-muted">
                            <i class="fas fa-sticky-note me-1"></i>
                            <?php echo htmlspecialchars($payment['notes']); ?>
                        </small>
                    </div>
                    <?php endif; ?>

                    <div class="payment-actions">
                        <?php if ($payment['invoice_id']): ?>
                            <a href="/vehicare_db/client/invoice-details.php?id=<?php echo $payment['invoice_id']; ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>View Invoice
                            </a>
                        <?php endif; ?>
                        
                        <a href="/vehicare_db/client/payment-receipt.php?id=<?php echo $payment['payment_id']; ?>" 
                           class="btn btn-outline-success btn-sm">
                            <i class="fas fa-download me-1"></i>Download Receipt
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&method=<?php echo $method_filter; ?>&date=<?php echo $date_filter; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i === (int)$page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&method=<?php echo $method_filter; ?>&date=<?php echo $date_filter; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&method=<?php echo $method_filter; ?>&date=<?php echo $date_filter; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <!-- No Payments -->
            <div class="no-payments">
                <i class="fas fa-credit-card"></i>
                <h3>No Payments Found</h3>
                <p>You haven't made any payments yet or no payments match your current filters.</p>
                <a href="/vehicare_db/client/invoices.php" class="btn btn-primary">
                    <i class="fas fa-file-invoice me-2"></i>View Invoices
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