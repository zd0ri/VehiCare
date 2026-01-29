<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';


$vehiclesQuery = $conn->query("
  SELECT v.*, c.full_name 
  FROM vehicles v
  LEFT JOIN clients c ON v.client_id = c.client_id
  ORDER BY v.vehicle_id DESC
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
    <a href="/vehicare_db/admins/vehicles.php" class="list-group-item active">
      <i class="fas fa-car"></i> Vehicles
    </a>
    <a href="/vehicare_db/admins/appointments.php" class="list-group-item">
      <i class="fas fa-calendar"></i> Appointments
    </a>
    <a href="/vehicare_db/admins/services.php" class="list-group-item">
      <i class="fas fa-cogs"></i> Services
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: 
  
  <div class="table-container">
    <div class="table-header">
      <h3>All Vehicles</h3>
      <button class="btn btn-primary btn-sm" onclick="alert('Add Vehicle feature coming soon')"><i class="fas fa-plus"></i> Add Vehicle</button>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Owner</th>
            <th>Plate Number</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Year</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($vehiclesQuery && $vehiclesQuery->num_rows > 0) {
            while ($vehicle = $vehiclesQuery->fetch_assoc()) {
              echo "<tr>
                <td>
                <td>{$vehicle['full_name']}</td>
                <td>{$vehicle['plate_number']}</td>
                <td>{$vehicle['car_brand']}</td>
                <td>{$vehicle['car_model']}</td>
                <td>{$vehicle['year_model']}</td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>Edit</button>
                    <button class='btn btn-danger btn-sm'>Delete</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='7' style='text-align: center; padding: 20px;'>No vehicles found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

