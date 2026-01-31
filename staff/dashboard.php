<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if technician is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get technician info
$techResult = $conn->query("SELECT * FROM users WHERE user_id = $user_id");
$technician = $techResult->fetch_assoc();

// Get all appointments assigned to this technician
$appointmentsResult = $conn->query("
    SELECT a.*, s.service_name, c.full_name AS client_name, u.email AS client_email, u.phone AS client_phone
    FROM appointments a
    JOIN assignments ass ON a.appointment_id = ass.appointment_id
    JOIN services s ON a.service_id = s.service_id
    JOIN clients c ON a.client_id = c.client_id
    JOIN users u ON c.client_id = u.user_id
    WHERE ass.staff_id = $user_id
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");

$appointments = [];
if ($appointmentsResult) {
    while ($row = $appointmentsResult->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// Group appointments by status
$statusGroups = [
    'pending' => [],
    'in-progress' => [],
    'completed' => [],
    'cancelled' => []
];

foreach ($appointments as $apt) {
    $status = strtolower($apt['status']);
    if (!isset($statusGroups[$status])) {
        $statusGroups['pending'][] = $apt;
    } else {
        $statusGroups[$status][] = $apt;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard - VehiCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8f0f7 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .tech-dashboard {
            max-width: 1600px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .tech-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc143c 0%, #a01030 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: 700;
        }

        .tech-info h1 {
            color: #1a1a1a;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .tech-info p {
            color: #666;
            font-size: 0.95em;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .btn-logout {
            background: #dc143c;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #a01030;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
        }

        .stat-icon.pending {
            background: #fff3cd;
            color: #ff9800;
        }

        .stat-icon.in-progress {
            background: #cfe2ff;
            color: #0066cc;
        }

        .stat-icon.completed {
            background: #d1e7dd;
            color: #27ae60;
        }

        .stat-content h3 {
            color: #1a1a1a;
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .stat-content p {
            color: #999;
            font-size: 0.9em;
        }

        .kanban-title {
            font-size: 1.8em;
            color: #1a1a1a;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .kanban-board {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .kanban-column {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            min-height: 500px;
        }

        .column-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .column-title {
            font-size: 1.1em;
            font-weight: 700;
            color: #1a1a1a;
        }

        .column-count {
            background: #f0f0f0;
            color: #666;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }

        .status-badge {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-badge.pending {
            background: #ff9800;
        }

        .status-badge.in-progress {
            background: #0066cc;
        }

        .status-badge.completed {
            background: #27ae60;
        }

        .status-badge.cancelled {
            background: #dc143c;
        }

        .appointment-card {
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .appointment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            border-color: #dc143c;
        }

        .card-service {
            font-size: 1.05em;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .card-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
            color: #666;
            margin-bottom: 8px;
        }

        .card-detail i {
            color: #dc143c;
            width: 14px;
        }

        .card-client {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-top: 12px;
            border-left: 3px solid #dc143c;
        }

        .card-client-name {
            font-weight: 600;
            color: #1a1a1a;
            font-size: 0.9em;
        }

        .card-client-info {
            font-size: 0.85em;
            color: #999;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .btn-action {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-start {
            background: #0066cc;
            color: white;
        }

        .btn-start:hover {
            background: #0052a3;
        }

        .btn-complete {
            background: #27ae60;
            color: white;
        }

        .btn-complete:hover {
            background: #229954;
        }

        .btn-update {
            background: #f5f5f5;
            color: #1a1a1a;
            border: 1px solid #ddd;
        }

        .btn-update:hover {
            background: #efefef;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .kanban-board {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="tech-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <div class="tech-avatar"><?php echo substr($technician['full_name'], 0, 1); ?></div>
                <div class="tech-info">
                    <h1><?php echo htmlspecialchars($technician['full_name']); ?></h1>
                    <p><?php echo htmlspecialchars($technician['email']); ?></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($statusGroups['pending']); ?></h3>
                    <p>Pending Appointments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon in-progress">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($statusGroups['in-progress']); ?></h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo count($statusGroups['completed']); ?></h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <h2 class="kanban-title">My Bookings</h2>
        <div class="kanban-board">
            <!-- Pending Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <span class="status-badge pending"></span>
                    <span class="column-title">Pending</span>
                    <span class="column-count"><?php echo count($statusGroups['pending']); ?></span>
                </div>
                <div class="appointments-list">
                    <?php if (count($statusGroups['pending']) > 0): ?>
                        <?php foreach ($statusGroups['pending'] as $apt): ?>
                        <div class="appointment-card">
                            <div class="card-service"><?php echo htmlspecialchars($apt['service_name']); ?></div>
                            <div class="card-detail">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                            </div>
                            <div class="card-detail">
                                <i class="fas fa-clock"></i>
                                <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                            </div>
                            <div class="card-client">
                                <div class="card-client-name"><?php echo htmlspecialchars($apt['client_name']); ?></div>
                                <div class="card-client-info"><?php echo htmlspecialchars($apt['client_phone']); ?></div>
                            </div>
                            <div class="card-actions">
                                <button class="btn-action btn-start" onclick="updateStatus(<?php echo $apt['appointment_id']; ?>, 'in-progress')">Start</button>
                                <button class="btn-action btn-update">Details</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No pending appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <span class="status-badge in-progress"></span>
                    <span class="column-title">In Progress</span>
                    <span class="column-count"><?php echo count($statusGroups['in-progress']); ?></span>
                </div>
                <div class="appointments-list">
                    <?php if (count($statusGroups['in-progress']) > 0): ?>
                        <?php foreach ($statusGroups['in-progress'] as $apt): ?>
                        <div class="appointment-card">
                            <div class="card-service"><?php echo htmlspecialchars($apt['service_name']); ?></div>
                            <div class="card-detail">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                            </div>
                            <div class="card-detail">
                                <i class="fas fa-clock"></i>
                                <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                            </div>
                            <div class="card-client">
                                <div class="card-client-name"><?php echo htmlspecialchars($apt['client_name']); ?></div>
                                <div class="card-client-info"><?php echo htmlspecialchars($apt['client_phone']); ?></div>
                            </div>
                            <div class="card-actions">
                                <button class="btn-action btn-complete" onclick="updateStatus(<?php echo $apt['appointment_id']; ?>, 'completed')">Complete</button>
                                <button class="btn-action btn-update">Details</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No appointments in progress</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Completed Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <span class="status-badge completed"></span>
                    <span class="column-title">Completed</span>
                    <span class="column-count"><?php echo count($statusGroups['completed']); ?></span>
                </div>
                <div class="appointments-list">
                    <?php if (count($statusGroups['completed']) > 0): ?>
                        <?php foreach ($statusGroups['completed'] as $apt): ?>
                        <div class="appointment-card">
                            <div class="card-service"><?php echo htmlspecialchars($apt['service_name']); ?></div>
                            <div class="card-detail">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                            </div>
                            <div class="card-detail">
                                <i class="fas fa-clock"></i>
                                <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                            </div>
                            <div class="card-client">
                                <div class="card-client-name"><?php echo htmlspecialchars($apt['client_name']); ?></div>
                                <div class="card-client-info"><?php echo htmlspecialchars($apt['client_phone']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No completed appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancelled Column -->
            <div class="kanban-column">
                <div class="column-header">
                    <span class="status-badge cancelled"></span>
                    <span class="column-title">Cancelled</span>
                    <span class="column-count"><?php echo count($statusGroups['cancelled']); ?></span>
                </div>
                <div class="appointments-list">
                    <?php if (count($statusGroups['cancelled']) > 0): ?>
                        <?php foreach ($statusGroups['cancelled'] as $apt): ?>
                        <div class="appointment-card">
                            <div class="card-service"><?php echo htmlspecialchars($apt['service_name']); ?></div>
                            <div class="card-detail">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                            </div>
                            <div class="card-detail">
                                <i class="fas fa-clock"></i>
                                <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                            </div>
                            <div class="card-client">
                                <div class="card-client-name"><?php echo htmlspecialchars($apt['client_name']); ?></div>
                                <div class="card-client-info"><?php echo htmlspecialchars($apt['client_phone']); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No cancelled appointments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(appointmentId, status) {
            if (confirm('Update appointment status to ' + status.toUpperCase() + '?')) {
                fetch('update_appointment_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'appointment_id=' + appointmentId + '&status=' + status
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error updating appointment');
                    }
                });
            }
        }
    </script>
</body>
</html>
