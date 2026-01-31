<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';

// Fetch services
$servicesQuery = $conn->query("SELECT * FROM services ORDER BY service_id DESC");
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
    <a href="/vehicare_db/admins/appointments.php" class="list-group-item">
      <i class="fas fa-calendar"></i> Appointments
    </a>
    <a href="/vehicare_db/admins/services.php" class="list-group-item active">
      <i class="fas fa-cogs"></i> Services
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: #1a1a1a; margin-bottom: 20px;">Manage Services</h1>
  
  <div class="table-container">
    <div class="table-header">
      <h3>Available Services</h3>
      <button class="btn btn-primary btn-sm" onclick="alert('Add Service feature coming soon')"><i class="fas fa-plus"></i> Add Service</button>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Service Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($servicesQuery && $servicesQuery->num_rows > 0) {
            while ($service = $servicesQuery->fetch_assoc()) {
              echo "<tr>
                <td>#{$service['service_id']}</td>
                <td>{$service['service_name']}</td>
                <td>" . substr($service['description'], 0, 50) . "...</td>
                <td>\${$service['price']}</td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>Edit</button>
                    <button class='btn btn-danger btn-sm'>Delete</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No services found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
