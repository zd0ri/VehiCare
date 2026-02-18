<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch staff
$staffQuery = $conn->query("SELECT * FROM staff ORDER BY staff_id DESC");

$page_title = "Staff";
$page_icon = "fas fa-user-tie";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Staff Management</h3>
        <button class="btn btn-primary" onclick="alert('Add staff feature coming soon')">
            <i class="fas fa-plus"></i> Add New Staff
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Position</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($staffQuery && $staffQuery->num_rows > 0): ?>
                    <?php while($staff = $staffQuery->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $staff['staff_id']; ?></td>
                        <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($staff['position']); ?></td>
                        <td><?php echo htmlspecialchars($staff['contact']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editStaff(<?php echo $staff['staff_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteStaff(<?php echo $staff['staff_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No staff members found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editStaff(id) {
    alert('Edit staff ' + id);
}

function deleteStaff(id) {
    if (confirm('Are you sure you want to delete this staff member?')) {
        alert('Delete staff ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
