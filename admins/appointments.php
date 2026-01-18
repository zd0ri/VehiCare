<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';

// Fetch appointments
$appointmentsQuery = $conn->query("
  SELECT a.*, c.full_name, v.car_brand, v.car_model, s.service_name
  FROM appointments a
  LEFT JOIN clients c ON a.client_id = c.client_id
  LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
  LEFT JOIN services s ON a.service_id = s.service_id
  ORDER BY a.appointment_date DESC
");
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="/vehicare_db/admins/clients.php" class="list-group-item">
      <i class="fas fa-users"></i> Clients
    </a>
    <a href="/vehicare_db/admins/vehicles.php" class="list-group-item">
      <i class="fas fa-car"></i> Vehicles
    </a>
    <a href="/vehicare_db/admins/appointments.php" class="list-group-item active">
      <i class="fas fa-calendar"></i> Appointments
    </a>
    <a href="/vehicare_db/admins/services.php" class="list-group-item">
      <i class="fas fa-cogs"></i> Services
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: #1a3a52; margin-bottom: 20px;">Manage Appointments</h1>
  
  <div class="table-container">
    <div class="table-header">
      <h3>All Appointments</h3>
      <a href="/vehicare_db/index.php#appointment" class="btn btn-primary btn-sm"><i class="fas fa-calendar-plus"></i> New Appointment</a>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
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
          <?php
          if ($appointmentsQuery && $appointmentsQuery->num_rows > 0) {
            while ($apt = $appointmentsQuery->fetch_assoc()) {
              $statusBadge = 'badge-primary';
              if ($apt['status'] == 'Completed') $statusBadge = 'badge-success';
              if ($apt['status'] == 'Cancelled') $statusBadge = 'badge-danger';
              
              echo "<tr>
                <td>#{$apt['appointment_id']}</td>
                <td>{$apt['full_name']}</td>
                <td>{$apt['car_brand']} {$apt['car_model']}</td>
                <td>{$apt['service_name']}</td>
                <td>" . date('M d, Y', strtotime($apt['appointment_date'])) . "</td>
                <td>" . date('h:i A', strtotime($apt['appointment_time'])) . "</td>
                <td><span class='badge {$statusBadge}'>{$apt['status']}</span></td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>Edit</button>
                    <button class='btn btn-danger btn-sm'>Cancel</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No appointments found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
