<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch all services
$servicesQuery = $conn->query("SELECT * FROM services ORDER BY service_name");

$page_title = "Services";
$page_icon = "fas fa-wrench";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Service Management</h3>
        <button class="btn btn-primary" onclick="alert('Add Service feature coming soon')">
            <i class="fas fa-plus"></i> Add Service
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
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
                <?php if ($servicesQuery && $servicesQuery->num_rows > 0): ?>
                    <?php while($service = $servicesQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $service['service_id']; ?></td>
                        <td><?php echo htmlspecialchars($service['service_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(substr($service['description'] ?? 'N/A', 0, 50)); ?>...</td>
                        <td>$<?php echo number_format($service['price'] ?? 0, 2); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editService(<?php echo $service['service_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteService(<?php echo $service['service_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No services found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editService(id) {
    alert('Edit service ' + id);
}

function deleteService(id) {
    if (confirm('Are you sure you want to delete this service?')) {
        alert('Delete service ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
