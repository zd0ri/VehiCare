<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Appointments & Bookings';
$page_icon = 'fas fa-calendar-check';
include __DIR__ . '/includes/admin_layout_header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $appointment_id = intval($_POST['appointment_id']);
        $new_status = $_POST['status'];
        $assigned_to = isset($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
        
        $query = "UPDATE appointments SET status = ?, ";
        $params = [$new_status];
        
        if ($assigned_to) {
            $query .= "assigned_to = ?, ";
            $params[] = $assigned_to;
        }
        
        $query .= "updated_at = NOW() WHERE appointment_id = ?";
        $params[] = $appointment_id;
        
        $stmt = $conn->prepare($query);
        $types = str_repeat('i', count($params) - 1) . 'i';
        array_unshift($params, $types);
        call_user_func_array([$stmt, 'bind_param'], $params);
        
        if ($stmt->execute()) {
            
            $conn->query("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, status, created_at) 
                         VALUES ({$_SESSION['user_id']}, 'UPDATE_APPOINTMENT', 'appointments', $appointment_id, 
                         JSON_OBJECT('status', '$new_status'), 'success', NOW())");
            
            
            $appointment = $conn->query("SELECT user_id FROM appointments WHERE appointment_id = $appointment_id")->fetch_assoc();
            if ($appointment) {
                $message = "Your appointment status has been updated to: " . ucfirst($new_status);
                $conn->query("INSERT INTO notifications (recipient_id, sender_id, title, message, notification_type, reference_id, reference_type, created_at)
                             VALUES ({$appointment['user_id']}, {$_SESSION['user_id']}, 'Appointment Status Update', '$message', 'appointment', $appointment_id, 'appointment', NOW())");
            }
            
            $_SESSION['success'] = "Appointment updated successfully!";
        }
    }
}


$appointments = $conn->query("
    SELECT a.*, 
           u.full_name as client_name, u.email as client_email,
           v.plate_number, v.make, v.model,
           s.service_name,
           staff.full_name as assigned_staff
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN users staff ON a.assigned_to = staff.user_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");


$staff = $conn->query("SELECT user_id, full_name FROM users WHERE role = 'staff' AND status = 'active'");
?>

<style>
    .appointments-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .appointment-card { border: 1px solid 
    .appointment-card:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.1); }
    .appointment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .appointment-type { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
    .appointment-type.appointment { background: 
    .appointment-type.walk-in { background: 
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .status-badge.pending { background: 
    .status-badge.confirmed { background: 
    .status-badge.in-progress { background: 
    .status-badge.completed { background: 
    .status-badge.cancelled { background: 
    .appointment-details { margin: 10px 0; }
    .detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 10px; }
    .detail-label { font-weight: bold; color: 
    .detail-value { color: 
    .action-buttons { margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap; }
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; text-decoration: none; display: inline-block; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-success { background: 
    .btn-success:hover { background: 
    .btn-warning { background: 
    .btn-warning:hover { background: 
    .btn-danger { background: 
    .btn-danger:hover { background: 
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .modal-content { background: white; margin: 5% auto; padding: 20px; border: 1px solid 
    .modal-close { color: 
    .modal-close:hover { color: 
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid 
    .filter-bar { background: 
    .filter-group { display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .alert-success { background: 
</style>

<div class="appointments-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Appointments & Walk-in Bookings</h2>
        <button onclick="openNewAppointmentModal()" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Appointment
        </button>
    </div>
    
    <div class="filter-bar">
        <div class="filter-group">
            <select id="filterStatus" onchange="filterAppointments()">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="in-progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select id="filterType" onchange="filterAppointments()">
                <option value="">All Types</option>
                <option value="appointment">Appointment</option>
                <option value="walk-in">Walk-in</option>
            </select>
            <input type="date" id="filterDate" onchange="filterAppointments()">
        </div>
    </div>
    
    <div id="appointmentsList">
        <?php while ($app = $appointments->fetch_assoc()): ?>
            <div class="appointment-card" data-status="<?php echo $app['status']; ?>" data-type="<?php echo $app['appointment_type']; ?>" data-date="<?php echo $app['appointment_date']; ?>">
                <div class="appointment-header">
                    <div>
                        <span class="appointment-type <?php echo $app['appointment_type']; ?>">
                            <?php echo strtoupper($app['appointment_type']); ?>
                        </span>
                        <span class="status-badge <?php echo $app['status']; ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </div>
                    <small style="color: 
                        <?php echo date('M d, Y H:i', strtotime($app['appointment_date'] . ' ' . $app['appointment_time'])); ?>
                    </small>
                </div>
                
                <div class="appointment-details">
                    <div class="detail-row">
                        <div>
                            <div class="detail-label">Client</div>
                            <div class="detail-value"><?php echo htmlspecialchars($app['client_name']); ?></div>
                        </div>
                        <div>
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($app['client_email']); ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div>
                            <div class="detail-label">Vehicle</div>
                            <div class="detail-value"><?php echo htmlspecialchars($app['make'] . ' ' . $app['model'] . ' (' . $app['plate_number'] . ')'); ?></div>
                        </div>
                        <div>
                            <div class="detail-label">Service</div>
                            <div class="detail-value"><?php echo $app['service_name'] ?? 'Not specified'; ?></div>
                        </div>
                    </div>
                    
                    <div class="detail-row">
                        <div>
                            <div class="detail-label">Assigned Staff</div>
                            <div class="detail-value"><?php echo $app['assigned_staff'] ?? 'Not assigned'; ?></div>
                        </div>
                        <div>
                            <div class="detail-label">Queue Number</div>
                            <div class="detail-value"><?php echo $app['queue_number'] ?? '-'; ?></div>
                        </div>
                    </div>
                    
                    <?php if ($app['notes']): ?>
                        <div>
                            <div class="detail-label">Notes</div>
                            <div class="detail-value"><?php echo htmlspecialchars($app['notes']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="editAppointment(<?php echo $app['appointment_id']; ?>)">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-success" onclick="assignStaff(<?php echo $app['appointment_id']; ?>)">
                        <i class="fas fa-user-tie"></i> Assign Staff
                    </button>
                    <button class="btn btn-warning" onclick="changeStatus(<?php echo $app['appointment_id']; ?>, '<?php echo $app['status']; ?>')">
                        <i class="fas fa-sync-alt"></i> Change Status
                    </button>
                    <button class="btn btn-danger" onclick="cancelAppointment(<?php echo $app['appointment_id']; ?>)">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal('statusModal')">&times;</span>
        <h3>Change Appointment Status</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="appointment_id" id="appointmentId">
            
            <div class="form-group">
                <label for="newStatus">New Status:</label>
                <select name="status" id="newStatus" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="in-progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="assignedTo">Assign to Staff (Optional):</label>
                <select name="assigned_to" id="assignedTo">
                    <option value="">Select Staff</option>
                    <?php 
                    $staff = $conn->query("SELECT user_id, full_name FROM users WHERE role = 'staff' AND status = 'active'");
                    while ($s = $staff->fetch_assoc()): ?>
                        <option value="<?php echo $s['user_id']; ?>"><?php echo htmlspecialchars($s['full_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="action-buttons">
                <button type="submit" class="btn btn-success">Update</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<script>
function changeStatus(appointmentId, currentStatus) {
    document.getElementById('appointmentId').value = appointmentId;
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function filterAppointments() {
    const status = document.getElementById('filterStatus').value;
    const type = document.getElementById('filterType').value;
    const date = document.getElementById('filterDate').value;
    
    const cards = document.querySelectorAll('.appointment-card');
    cards.forEach(card => {
        let show = true;
        
        if (status && card.dataset.status !== status) show = false;
        if (type && card.dataset.type !== type) show = false;
        if (date && card.dataset.date !== date) show = false;
        
        card.style.display = show ? 'block' : 'none';
    });
}

function assignStaff(appointmentId) {
    
    alert('Assign staff functionality - implement staff selection modal');
}

function editAppointment(appointmentId) {
    window.location.href = '/vehicare_db/admins/edit_appointment.php?id=' + appointmentId;
}

function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        document.getElementById('appointmentId').value = appointmentId;
        document.getElementById('newStatus').value = 'cancelled';
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="update_status"><input type="hidden" name="appointment_id" value="' + appointmentId + '"><input type="hidden" name="status" value="cancelled">';
        document.body.appendChild(form);
        form.submit();
    }
}

function openNewAppointmentModal() {
    alert('Open new appointment booking modal - implement booking form');
}

window.onclick = function(event) {
    let modal = document.getElementById('statusModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

