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
$year_filter = $_GET['year'] ?? 'all';
$service_filter = $_GET['service'] ?? 'all';
$sort_order = $_GET['sort'] ?? 'desc';

// Build query conditions for appointment history
$conditions = ["a.client_id = $client_id"];
$params = [];
$types = "";

// Filter by status (focus on completed/past appointments)
if ($status_filter === 'completed') {
    $conditions[] = "a.status = 'completed'";
} elseif ($status_filter === 'cancelled') {
    $conditions[] = "a.status = 'cancelled'";
} elseif ($status_filter === 'no_show') {
    $conditions[] = "a.status = 'no_show'";
} elseif ($status_filter !== 'all') {
    $conditions[] = "a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Filter by year
if ($year_filter !== 'all') {
    $conditions[] = "YEAR(a.appointment_date) = ?";
    $params[] = $year_filter;
    $types .= "i";
}

// Filter by service
if ($service_filter !== 'all') {
    $conditions[] = "a.service_id = ?";
    $params[] = $service_filter;
    $types .= "i";
}

$where_clause = implode(' AND ', $conditions);
$order_direction = ($sort_order === 'asc') ? 'ASC' : 'DESC';

// Get appointment history with pagination
$page = $_GET['page'] ?? 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Try query with payment_status first
try {
    $history_query = "
        SELECT a.*, s.service_name, s.price as base_price, s.description,
               v.plate_number, v.car_brand, v.car_model, v.year_model,
               st.full_name as technician_name,
               i.invoice_id, i.grand_total as invoice_total, i.payment_status
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        LEFT JOIN assignments ass ON a.appointment_id = ass.appointment_id
        LEFT JOIN staff st ON ass.staff_id = st.staff_id
        LEFT JOIN invoices i ON a.appointment_id = i.appointment_id
        WHERE $where_clause
        ORDER BY a.appointment_date $order_direction, a.appointment_time $order_direction
        LIMIT $per_page OFFSET $offset
    ";

    if (!empty($params)) {
        $stmt = $conn->prepare($history_query);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
        $stmt->execute();
        $appointments = $stmt->get_result();
    } else {
        $appointments = $conn->query($history_query);
    }
} catch (Exception $e) {
    // Fallback query without payment_status column
    $history_query = "
        SELECT a.*, s.service_name, s.price as base_price, s.description,
               v.plate_number, v.car_brand, v.car_model, v.year_model,
               st.full_name as technician_name,
               i.invoice_id, i.grand_total as invoice_total
        FROM appointments a
        JOIN services s ON a.service_id = s.service_id
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        LEFT JOIN assignments ass ON a.appointment_id = ass.appointment_id
        LEFT JOIN staff st ON ass.staff_id = st.staff_id
        LEFT JOIN invoices i ON a.appointment_id = i.appointment_id
        WHERE $where_clause
        ORDER BY a.appointment_date $order_direction, a.appointment_time $order_direction
        LIMIT $per_page OFFSET $offset
    ";

    if (!empty($params)) {
        $stmt = $conn->prepare($history_query);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
        $stmt->execute();
        $appointments = $stmt->get_result();
    } else {
        $appointments = $conn->query($history_query);
    }
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM appointments a WHERE $where_clause";
if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    call_user_func_array([$count_stmt, 'bind_param'], array_merge([$types], $params));
    $count_stmt->execute();
    $total_appointments = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_appointments = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_appointments / $per_page);

// Get available years for filter
$years = $conn->query("
    SELECT DISTINCT YEAR(appointment_date) as year 
    FROM appointments 
    WHERE client_id = $client_id 
    ORDER BY year DESC
");

// Get available services for filter
$services = $conn->query("
    SELECT DISTINCT s.service_id, s.service_name 
    FROM appointments a 
    JOIN services s ON a.service_id = s.service_id 
    WHERE a.client_id = $client_id 
    ORDER BY s.service_name
");

// Get summary statistics
$stats = [];
$stats['total_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE client_id = $client_id")->fetch_assoc()['count'] ?? 0;
$stats['completed_appointments'] = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE client_id = $client_id AND status = 'completed'")->fetch_assoc()['count'] ?? 0;
$stats['total_spent'] = $conn->query("
    SELECT SUM(i.grand_total) as total 
    FROM appointments a 
    JOIN invoices i ON a.appointment_id = i.appointment_id 
    WHERE a.client_id = $client_id AND a.status = 'completed'
")->fetch_assoc()['total'] ?? 0;

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History - VehiCare</title>
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

        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 40px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-weight: 700;
            margin: 0;
            font-size: 2.5rem;
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
        }

        .stat-icon.total { background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); }
        .stat-icon.completed { background: linear-gradient(135deg, #00b894 0%, #00a085 100%); }
        .stat-icon.spent { background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3436;
            margin: 0;
        }

        .stat-label {
            color: #636e72;
            font-size: 0.9rem;
            margin: 5px 0 0 0;
        }

        .filters-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .filters-title {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-timeline {
            position: relative;
        }

        .timeline-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            margin-left: 30px;
            border-left: 4px solid #ddd;
            transition: all 0.3s ease;
        }

        .timeline-item:hover {
            border-left-color: #74b9ff;
            transform: translateX(5px);
        }

        .timeline-item.completed {
            border-left-color: #00b894;
        }

        .timeline-item.cancelled {
            border-left-color: #e17055;
        }

        .timeline-item.no_show {
            border-left-color: #fdcb6e;
        }

        .timeline-date {
            position: absolute;
            left: -90px;
            top: 25px;
            width: 80px;
            text-align: center;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            padding: 8px;
            border-radius: 10px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .appointment-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .appointment-title {
            font-weight: 600;
            color: #2d3436;
            font-size: 1.2rem;
            margin: 0;
        }

        .appointment-badges {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-completed { background: #d1f2eb; color: #00b894; }
        .status-cancelled { background: #ffeaa7; color: #e17055; }
        .status-no_show { background: #fab1a0; color: #e17055; }
        .status-pending { background: #74b9ff; color: white; }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
        }

        .detail-content h6 {
            margin: 0;
            font-size: 0.8rem;
            color: #636e72;
            font-weight: 500;
        }

        .detail-content p {
            margin: 0;
            font-weight: 600;
            color: #2d3436;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .btn-filter {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(116, 185, 255, 0.4);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #636e72;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .timeline-date {
                display: none;
            }

            .timeline-item {
                margin-left: 0;
            }

            .appointment-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="history-container">
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-history"></i> Service History</h1>
                <p>Complete record of your vehicle service appointments</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="stat-value"><?php echo $stats['total_appointments']; ?></h3>
                <p class="stat-label">Total Appointments</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="stat-value"><?php echo $stats['completed_appointments']; ?></h3>
                <p class="stat-label">Completed Services</p>
            </div>
            <div class="stat-card">
                <div class="stat-icon spent">
                    <i class="fas fa-peso-sign"></i>
                </div>
                <h3 class="stat-value">₱<?php echo number_format($stats['total_spent'], 2); ?></h3>
                <p class="stat-label">Total Spent</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-card">
            <h5 class="filters-title">
                <i class="fas fa-filter"></i>
                Filter History
            </h5>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="no_show" <?php echo $status_filter === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select">
                        <option value="all" <?php echo $year_filter === 'all' ? 'selected' : ''; ?>>All Years</option>
                        <?php while ($year = $years->fetch_assoc()): ?>
                            <option value="<?php echo $year['year']; ?>" <?php echo $year_filter == $year['year'] ? 'selected' : ''; ?>>
                                <?php echo $year['year']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Service</label>
                    <select name="service" class="form-select">
                        <option value="all" <?php echo $service_filter === 'all' ? 'selected' : ''; ?>>All Services</option>
                        <?php while ($service = $services->fetch_assoc()): ?>
                            <option value="<?php echo $service['service_id']; ?>" <?php echo $service_filter == $service['service_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($service['service_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sort Order</label>
                    <select name="sort" class="form-select">
                        <option value="desc" <?php echo $sort_order === 'desc' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="asc" <?php echo $sort_order === 'asc' ? 'selected' : ''; ?>>Oldest First</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="/vehicare_db/client/appointment-history.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-refresh"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Timeline -->
        <div class="history-timeline">
            <?php if ($appointments && $appointments->num_rows > 0): ?>
                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                    <div class="timeline-item <?php echo $appointment['status']; ?>">
                        <div class="timeline-date">
                            <?php echo date('M j', strtotime($appointment['appointment_date'])); ?><br>
                            <?php echo date('Y', strtotime($appointment['appointment_date'])); ?>
                        </div>
                        
                        <div class="appointment-header">
                            <h4 class="appointment-title">
                                <?php echo htmlspecialchars($appointment['service_name']); ?>
                            </h4>
                            <div class="appointment-badges">
                                <span class="status-badge status-<?php echo $appointment['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                </span>
                                <?php if ($appointment['invoice_total']): ?>
                                    <span class="badge bg-success">₱<?php echo number_format($appointment['invoice_total'], 2); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="appointment-details">
                            <div class="detail-item">
                                <div class="detail-icon" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                                    <i class="fas fa-calendar"></i>
                                </div>
                                <div class="detail-content">
                                    <h6>Date & Time</h6>
                                    <p><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?><br>
                                       <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon" style="background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="detail-content">
                                    <h6>Vehicle</h6>
                                    <p><?php echo htmlspecialchars($appointment['car_brand'] . ' ' . $appointment['car_model'] . ' (' . $appointment['year_model'] . ')'); ?><br>
                                       <small><?php echo htmlspecialchars($appointment['plate_number']); ?></small></p>
                                </div>
                            </div>

                            <?php if ($appointment['technician_name']): ?>
                            <div class="detail-item">
                                <div class="detail-icon" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                                    <i class="fas fa-user-wrench"></i>
                                </div>
                                <div class="detail-content">
                                    <h6>Technician</h6>
                                    <p><?php echo htmlspecialchars($appointment['technician_name']); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($appointment['base_price']): ?>
                            <div class="detail-item">
                                <div class="detail-icon" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                                    <i class="fas fa-peso-sign"></i>
                                </div>
                                <div class="detail-content">
                                    <h6>Service Price</h6>
                                    <p>₱<?php echo number_format($appointment['base_price'], 2); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h4>No appointment history found</h4>
                    <p>You haven't had any appointments yet or no appointments match your current filters.</p>
                    <a href="/vehicare_db/client/book-appointment.php" class="btn btn-filter mt-3">
                        <i class="fas fa-plus"></i> Book Your First Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-container">
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include __DIR__ . '/../includes/footer.php'; ?>