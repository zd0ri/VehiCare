<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch all vehicles with client information
$vehiclesQuery = $conn->query("
  SELECT v.*, u.full_name as client_name
  FROM vehicles v
  LEFT JOIN users u ON v.client_id = u.user_id
  ORDER BY v.car_brand
");

$page_title = "Vehicles";
$page_icon = "fas fa-car";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Vehicle Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#vehicleModal">
            <i class="fas fa-plus"></i> Add Vehicle
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
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
                <?php if ($vehiclesQuery && $vehiclesQuery->num_rows > 0): ?>
                    <?php while($vehicle = $vehiclesQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $vehicle['vehicle_id']; ?></td>
                        <td><?php echo htmlspecialchars($vehicle['client_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['plate_number'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['car_brand'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['car_model'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['year_model'] ?? 'N/A'); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No vehicles found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
function editVehicle(id) {
    alert('Edit vehicle ' + id);
}

function deleteVehicle(id) {
    if (confirm('Are you sure you want to delete this vehicle?')) {
        alert('Delete vehicle ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
