<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch appointments
$appointmentsQuery = $conn->query("
  SELECT a.*, c.full_name, v.car_brand, v.car_model, s.service_name
  FROM appointments a
  LEFT JOIN users c ON a.client_id = c.user_id
  LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
  LEFT JOIN services s ON a.service_id = s.service_id
  ORDER BY a.appointment_date DESC
");

$page_title = "Appointments";
$page_icon = "fas fa-calendar";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Alert Messages -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Appointment Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal">
            <i class="fas fa-plus"></i> New Appointment
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Vehicle</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointmentsQuery && $appointmentsQuery->num_rows > 0): ?>
                    <?php while($appointment = $appointmentsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $appointment['appointment_id']; ?></td>
                        <td><?php echo htmlspecialchars($appointment['full_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(($appointment['car_brand'] ?? '') . ' ' . ($appointment['car_model'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($appointment['service_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_time'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $appointment['status'] == 'Confirmed' ? 'success' : ($appointment['status'] == 'Pending' ? 'warning' : 'secondary'); ?>">
                                <?php echo htmlspecialchars($appointment['status'] ?? 'Unknown'); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No appointments found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editAppointment(id) {
    // Add edit functionality
    alert('Edit appointment ' + id);
}

function deleteAppointment(id) {
    if (confirm('Are you sure you want to delete this appointment?')) {
        // Add delete functionality
        alert('Delete appointment ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
