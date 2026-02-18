<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];

// Handle appointment cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['appointment_id'];
    
    // Update appointment status to cancelled
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'cancelled', updated_date = CURRENT_TIMESTAMP 
        WHERE appointment_id = ? AND client_id = ?
    ");
    $stmt->bind_param("ii", $appointment_id, $client_id);
    
    if ($stmt->execute()) {
        log_event($client_id, "appointment_cancelled", "Cancelled appointment #$appointment_id");
        $_SESSION['success'] = "Appointment cancelled successfully.";
    } else {
        $_SESSION['error'] = "Failed to cancel appointment.";
    }
    
    header("Location: /vehicare_db/client/appointments.php");
    exit;
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

// Build query conditions
$conditions = ["a.client_id = $client_id"];
$params = [];
$types = "";

if ($status_filter !== 'all') {
    $conditions[] = "a.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($date_filter === 'upcoming') {
    $conditions[] = "a.appointment_date >= CURDATE()";
} elseif ($date_filter === 'past') {
    $conditions[] = "a.appointment_date < CURDATE()";
}

$where_clause = implode(' AND ', $conditions);

// Get appointments with pagination
$page = $_GET['page'] ?? 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$appointments_query = "
    SELECT a.*, s.service_name, s.price as base_price, s.description,
           v.plate_number, v.car_brand, v.car_model, v.year_model,
           st.full_name as technician_name
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN assignments ass ON a.appointment_id = ass.appointment_id
    LEFT JOIN staff st ON ass.staff_id = st.staff_id
    WHERE $where_clause
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT $per_page OFFSET $offset
";

if (!empty($params)) {
    $stmt = $conn->prepare($appointments_query);
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    $stmt->execute();
    $appointments = $stmt->get_result();
} else {
    $appointments = $conn->query($appointments_query);
}

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM appointments a
    WHERE $where_clause
";

if (!empty($params)) {
    $count_stmt = $conn->prepare($count_query);
    call_user_func_array([$count_stmt, 'bind_param'], array_merge([$types], $params));
    $count_stmt->execute();
    $total_appointments = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_appointments = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_appointments / $per_page);

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - VehiCare</title>
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

        .appointments-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            margin: 0;
            font-weight: 700;
        }

        .filters-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .filters-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-select, .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 15px;
        }

        .form-select:focus, .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 10px 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border: none;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
        }

        .appointment-card:hover {
            transform: translateY(-2px);
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .appointment-date {
            font-size: 1.2em;
            font-weight: 700;
            color: #2c3e50;
        }

        .appointment-time {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-in-progress { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-no-show { background: #e2e3e5; color: #383d41; }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            color: white;
        }

        .detail-content h6 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .detail-content p {
            margin: 0;
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .appointment-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .btn-sm {
            padding: 8px 15px;
            font-size: 0.85em;
        }

        .no-appointments {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .no-appointments i {
            font-size: 4em;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .pagination {
            justify-content: center;
            margin-top: 30px;
        }

        .page-link {
            border-radius: 10px;
            margin: 0 3px;
            border: 2px solid #e9ecef;
            color: #3498db;
            padding: 8px 15px;
        }

        .page-item.active .page-link {
            background: #3498db;
            border-color: #3498db;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .appointment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .appointment-details {
                grid-template-columns: 1fr;
            }

            .appointment-actions {
                justify-content: stretch;
            }

            .appointment-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="appointments-container">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><i class="fas fa-calendar-check me-3"></i>My Appointments</h1>
                <p class="mb-0">Manage and track your service appointments</p>
            </div>
            <a href="/vehicare_db/client/book-appointment.php" class="btn btn-light">
                <i class="fas fa-plus me-2"></i>Book New Appointment
            </a>
        </div>

        <!-- Display Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters-card">
            <form method="GET" class="filters-row">
                <div class="filter-group">
                    <label>Status Filter</label>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="in-progress" <?php echo $status_filter === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Date Filter</label>
                    <select name="date" class="form-select">
                        <option value="all" <?php echo $date_filter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                        <option value="upcoming" <?php echo $date_filter === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                        <option value="past" <?php echo $date_filter === 'past' ? 'selected' : ''; ?>>Past</option>
                    </select>
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Appointments List -->
        <?php if ($appointments && $appointments->num_rows > 0): ?>
            <?php while ($appointment = $appointments->fetch_assoc()): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div>
                            <div class="appointment-date">
                                <?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?>
                            </div>
                            <div class="appointment-time">
                                <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $appointment['status']; ?>">
                            <?php echo ucfirst(str_replace('-', ' ', $appointment['status'])); ?>
                        </span>
                    </div>

                    <div class="appointment-details">
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                                <i class="fas fa-wrench"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Service</h6>
                                <p><?php echo htmlspecialchars($appointment['service_name']); ?></p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Vehicle</h6>
                                <p><?php echo htmlspecialchars($appointment['plate_number'] . ' • ' . $appointment['car_brand'] . ' ' . $appointment['car_model']); ?></p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Estimated Cost</h6>
                                <p>₱<?php echo number_format($appointment['base_price'], 2); ?></p>
                            </div>
                        </div>

                        <?php if ($appointment['technician_name']): ?>
                        <div class="detail-item">
                            <div class="detail-icon" style="background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);">
                                <i class="fas fa-user-hard-hat"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Technician</h6>
                                <p><?php echo htmlspecialchars($appointment['technician_name']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="appointment-actions">
                        <a href="/vehicare_db/client/appointment-details.php?id=<?php echo $appointment['appointment_id']; ?>" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Details
                        </a>
                        
                        <?php if (in_array($appointment['status'], ['pending', 'confirmed'])): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                <button type="submit" name="cancel_appointment" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($appointment['status'] === 'completed'): ?>
                            <a href="/vehicare_db/client/reviews.php?appointment=<?php echo $appointment['appointment_id']; ?>" 
                               class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-star me-1"></i>Rate Service
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
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
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&status=<?php echo $status_filter; ?>&date=<?php echo $date_filter; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <!-- No Appointments -->
            <div class="no-appointments">
                <i class="fas fa-calendar-times"></i>
                <h3>No Appointments Found</h3>
                <p>You don't have any appointments matching your current filters.</p>
                <a href="/vehicare_db/client/book-appointment.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Book Your First Appointment
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