<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all users
$users_query = "SELECT user_id, username, email, full_name, phone, role, status, created_date, last_login FROM users ORDER BY created_date DESC";
$users_result = $conn->query($users_query);
$users = [];

if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Count users by role
$admin_count = 0;
$staff_count = 0;
$client_count = 0;

foreach ($users as $user) {
    if ($user['role'] === 'admin') {
        $admin_count++;
    } elseif ($user['role'] === 'staff') {
        $staff_count++;
    } else {
        $client_count++;
    }
}

$page_title = "Users";
$page_icon = "fas fa-users";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-2x mb-2 text-danger"></i>
                    <h3 class="mb-1"><?php echo $admin_count; ?></h3>
                    <p class="text-muted mb-0">Administrators</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie fa-2x mb-2" style="color: #dc143c;"></i>
                    <h3 class="mb-1"><?php echo $staff_count; ?></h3>
                    <p class="text-muted mb-0">Staff Members</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2 text-success"></i>
                    <h3 class="mb-1"><?php echo $client_count; ?></h3>
                    <p class="text-muted mb-0">Clients</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" id="filterSearch" class="form-control" placeholder="Search users..." onkeyup="filterTable()">
        </div>
        <div class="col-md-4">
            <select id="filterRole" class="form-control" onchange="filterTable()">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="client">Client</option>
            </select>
        </div>
        <div class="col-md-4">
            <select id="filterStatus" class="form-control" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div class="table-responsive">
        <table class="table table-hover" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="user-row" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                    <td><?php echo $user['user_id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="user-avatar me-2">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($user['email']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></small>
                    </td>
                    <td>
                        <span class="badge bg-<?php 
                            echo $user['role'] === 'admin' ? 'danger' : 
                                ($user['role'] === 'staff' ? 'info' : 'success'); 
                        ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-<?php 
                            echo $user['status'] === 'active' ? 'success' : 
                                ($user['status'] === 'suspended' ? 'warning' : 'secondary'); 
                        ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_date'])); ?></td>
                    <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" onclick="viewUser(<?php echo $user['user_id']; ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['user_id']; ?>)">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dc143c 0%, #a01030 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 16px;
}
</style>

<script>
function filterTable() {
    const searchInput = document.getElementById('filterSearch').value.toLowerCase();
    const roleFilter = document.getElementById('filterRole').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.user-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const role = row.dataset.role;
        const status = row.dataset.status;

        const matchesSearch = text.includes(searchInput);
        const matchesRole = !roleFilter || role === roleFilter;
        const matchesStatus = !statusFilter || status === statusFilter;

        row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
    });
}

function viewUser(id) {
    alert('View user ' + id);
}

function editUser(id) {
    alert('Edit user ' + id);
}

function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user?')) {
        alert('Delete user ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
