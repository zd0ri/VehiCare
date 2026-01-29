<?php

session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$message = '';
$error = '';


$filter_status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$where_clause = "WHERE 1=1";

if ($filter_status !== 'all') {
    $where_clause .= " AND a.status = '$filter_status'";
}


$query = "SELECT a.*, u.full_name as customer_name, u.email as customer_email,
                 v.vehicle_type, v.make, v.model, v.plate_number,
                 s.service_name, st.full_name as staff_name
          FROM appointments a
          JOIN users u ON a.user_id = u.user_id
          LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
          LEFT JOIN services s ON a.service_id = s.service_id
          LEFT JOIN users st ON a.assigned_to = st.user_id
          $where_clause
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$result = $conn->query($query);
$appointments = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
    $apt_id = intval($_GET['id']);
    $new_status = trim($_GET['new_status']);
    
    $allowed_statuses = ['pending', 'confirmed', 'in-progress', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $update = "UPDATE appointments SET status = '$new_status' WHERE appointment_id = $apt_id";
        if ($conn->query($update)) {
            $message = "Appointment status updated!";
            header("Refresh: 1; url=/vehicare_db/admins/manage_appointments.php?status=" . $filter_status);
        } else {
            $error = "Failed to update status.";
        }
    }
}

$page_title = 'Manage Appointments';
$page_icon = 'fas fa-calendar-alt';
include __DIR__ . '/includes/admin_layout_header.php';
?>

<div style="flex: 1; overflow-y: auto; padding: 30px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <div>
            <h1 style="margin: 0 0 5px 0; font-size: 28px;"><i class="fas fa-calendar-alt"></i> Manage Appointments</h1>
            <p style="margin: 0; opacity: 0.9;">View and manage customer service appointments</p>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div style="background: 
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div style="background: 
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Filter Buttons -->
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
        <h6 style="margin: 0 0 15px 0; color: #333;">Filter by Status</h6>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/vehicare_db/admins/manage_appointments.php?status=all" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'all' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'all' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                All
            </a>
            <a href="/vehicare_db/admins/manage_appointments.php?status=pending" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'pending' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'pending' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                Pending
            </a>
            <a href="/vehicare_db/admins/manage_appointments.php?status=confirmed" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'confirmed' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'confirmed' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                Confirmed
            </a>
            <a href="/vehicare_db/admins/manage_appointments.php?status=in-progress" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'in-progress' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'in-progress' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                In Progress
            </a>
            <a href="/vehicare_db/admins/manage_appointments.php?status=completed" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'completed' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'completed' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                Completed
            </a>
            <a href="/vehicare_db/admins/manage_appointments.php?status=cancelled" 
               style="padding: 8px 15px; border-radius: 6px; background: <?php echo $filter_status === 'cancelled' ? '#2d5a7b' : '#f0f0f0'; ?>; color: <?php echo $filter_status === 'cancelled' ? 'white' : '#333'; ?>; text-decoration: none; cursor: pointer;">
                Cancelled
            </a>
        </div>
    </div>

    <!-- Appointments Table -->
    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #e0e0e0;">
                        <th style="padding: 15px 20px; text-align: left; color: #333; font-weight: 600;">Date & Time</th>
                        <th style="padding: 15px 20px; text-align: left; color: #333; font-weight: 600;">Customer</th>
                        <th style="padding: 15px 20px; text-align: left; color: #333; font-weight: 600;">Vehicle</th>
                        <th style="padding: 15px 20px; text-align: left; color: #333; font-weight: 600;">Service</th>
                        <th style="padding: 15px 20px; text-align: left; color: #333; font-weight: 600;">Type</th>
                        <th style="padding: 15px 20px; text-align: center; color: #333; font-weight: 600;">Status</th>
                        <th style="padding: 15px 20px; text-align: center; color: #333; font-weight: 600;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $apt): ?>
                    <tr style="border-bottom: 1px solid #e0e0e0;">
                        <td style="padding: 15px 20px; color: #333;">
                            <strong><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></strong><br>
                            <small style="color: #999;"><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></small>
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <strong><?php echo htmlspecialchars($apt['customer_name']); ?></strong><br>
                            <small style="color: #999;"><?php echo htmlspecialchars($apt['customer_email']); ?></small>
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <?php echo htmlspecialchars($apt['vehicle_type'] . ' - ' . $apt['make']); ?><br>
                            <small style="color: #999;"><?php echo htmlspecialchars($apt['plate_number']); ?></small>
                        </td>
                        <td style="padding: 15px 20px; color: #333;">
                            <?php echo $apt['service_name'] ? htmlspecialchars($apt['service_name']) : 'General Service'; ?>
                        </td>
                        <td style="padding: 15px 20px;">
                            <span style="background: <?php echo $apt['appointment_type'] === 'appointment' ? '#e74c3c' : '#3498db'; ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                <?php echo ucfirst($apt['appointment_type']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; text-align: center; color: #333;">
                            <?php echo $apt['staff_name'] ? htmlspecialchars($apt['staff_name']) : '–'; ?>
                        </td>
                        <td style="padding: 15px 20px; text-align: center;">
                            <span style="background: <?php 
                                switch ($apt['status']) {
                                    case 'pending': echo '#ffc107';
                                    break;
                                    case 'confirmed': echo '#17a2b8';
                                    break;
                                    case 'in-progress': echo '#28a745';
                                    break;
                                    case 'completed': echo '#20c997';
                                    break;
                                    case 'cancelled': echo '#dc3545';
                                    break;
                                    default: echo '#6c757d';
                                }
                            ?>; color: white; padding: 6px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;"><?php 
                                echo ucfirst(str_replace('-', ' ', $apt['status'])); ?>
                                    case 'pending': echo '
                                    case 'confirmed': echo '
                                    case 'in-progress': echo '
                                    case 'completed': echo '
                                    case 'cancelled': echo '
                                }
                            ?>; padding: 6px 12px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo strtoupper($apt['status']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px 20px; text-align: center; font-size: 13px;">
                            <a href="/vehicare_db/admins/appointment_detail.php?id=<?php echo $apt['appointment_id']; ?>" style="color: 
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (count($appointments) === 0): ?>
    <div style="background: white; padding: 60px 20px; border-radius: 8px; text-align: center;">
        <i class="fas fa-calendar-alt" style="font-size: 48px; color: 
        <h4 style="color: 
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

