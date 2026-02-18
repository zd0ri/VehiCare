<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all technicians from users table where role = 'staff'
$technicians = $conn->query("SELECT * FROM users WHERE role = 'staff' ORDER BY user_id DESC");

$page_title = "Technicians";
$page_icon = "fas fa-tools";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Technician Management</h3>
        <button class="btn btn-primary" onclick="alert('Add Technician feature coming soon')">
            <i class="fas fa-plus"></i> Add Technician
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($technicians && $technicians->num_rows > 0): ?>
                    <?php while($tech = $technicians->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $tech['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($tech['full_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($tech['email']); ?></td>
                        <td><?php echo htmlspecialchars($tech['phone'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $tech['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($tech['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($tech['created_date'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editTechnician(<?php echo $tech['user_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTechnician(<?php echo $tech['user_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No technicians found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editTechnician(id) {
    alert('Edit technician ' + id);
}

function deleteTechnician(id) {
    if (confirm('Are you sure you want to delete this technician?')) {
        alert('Delete technician ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>