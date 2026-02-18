<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch all clients
$clientsQuery = $conn->query("SELECT * FROM users WHERE role = 'client' ORDER BY full_name");

$page_title = "Clients";
$page_icon = "fas fa-users";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Client Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
            <i class="fas fa-plus"></i> Add Client
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
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($clientsQuery && $clientsQuery->num_rows > 0): ?>
                    <?php while($client = $clientsQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $client['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($client['full_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($client['address'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $client['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($client['status']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editClient(<?php echo $client['user_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteClient(<?php echo $client['user_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No clients found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function editClient(id) {
    alert('Edit client ' + id);
}

function deleteClient(id) {
    if (confirm('Are you sure you want to delete this client?')) {
        alert('Delete client ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
