<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';

// Fetch staff
$staffQuery = $conn->query("SELECT * FROM staff ORDER BY staff_id DESC");
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="/vehicare_db/admins/staff.php" class="list-group-item active">
      <i class="fas fa-people-group"></i> Staff
    </a>
    <a href="/vehicare_db/admins/payments.php" class="list-group-item">
      <i class="fas fa-money-bill"></i> Payments
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: #1a3a52; margin-bottom: 20px;">Manage Staff</h1>
  
  <div class="table-container">
    <div class="table-header">
      <h3>All Staff Members</h3>
      <button class="btn btn-primary btn-sm" onclick="alert('Add Staff feature coming soon')"><i class="fas fa-plus"></i> Add Staff</button>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
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
          <?php
          if ($staffQuery && $staffQuery->num_rows > 0) {
            while ($staff = $staffQuery->fetch_assoc()) {
              echo "<tr>
                <td>#{$staff['staff_id']}</td>
                <td>{$staff['full_name']}</td>
                <td>{$staff['position']}</td>
                <td>{$staff['contact']}</td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>Edit</button>
                    <button class='btn btn-danger btn-sm'>Delete</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No staff members found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
