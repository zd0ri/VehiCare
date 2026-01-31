<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';

// Fetch parts/inventory
$partsQuery = $conn->query("SELECT * FROM parts ORDER BY part_id DESC");
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="/vehicare_db/admins/parts.php" class="list-group-item active">
      <i class="fas fa-box"></i> Parts & Inventory
    </a>
    <a href="/vehicare_db/admins/staff.php" class="list-group-item">
      <i class="fas fa-people-group"></i> Staff
    </a>
    <a href="/vehicare_db/admins/payments.php" class="list-group-item">
      <i class="fas fa-money-bill"></i> Payments
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: #1a1a1a; margin-bottom: 20px;">Parts & Inventory</h1>
  
  <div class="table-container">
    <div class="table-header">
      <h3>All Parts</h3>
      <button class="btn btn-primary btn-sm" onclick="alert('Add Part feature coming soon')"><i class="fas fa-plus"></i> Add Part</button>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Part Name</th>
            <th>Brand</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($partsQuery && $partsQuery->num_rows > 0) {
            while ($part = $partsQuery->fetch_assoc()) {
              echo "<tr>
                <td>#{$part['part_id']}</td>
                <td>{$part['part_name']}</td>
                <td>{$part['brand']}</td>
                <td>\${$part['price']}</td>
                <td><span class='badge badge-" . ($part['stock'] > 10 ? 'success' : 'warning') . "'>{$part['stock']} units</span></td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>Edit</button>
                    <button class='btn btn-danger btn-sm'>Delete</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No parts found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
