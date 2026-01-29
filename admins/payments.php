<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';


$paymentsQuery = $conn->query("
  SELECT i.*, a.appointment_id, c.full_name
  FROM invoices i
  LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
  LEFT JOIN clients c ON a.client_id = c.client_id
  ORDER BY i.invoice_date DESC
");
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item">
      <i class="fas fa-chart-line"></i> Dashboard
    </a>
    <a href="/vehicare_db/admins/payments.php" class="list-group-item active">
      <i class="fas fa-money-bill"></i> Payments
    </a>
  </div>
</div>

<div class="admin-main-content">
  <h1 style="color: 
  
  <div class="dashboard-stats" style="margin-bottom: 30px;">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-money-bill"></i></div>
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value">
        $<?php
        $revenueResult = $conn->query("SELECT SUM(grand_total) as total FROM invoices")->fetch_assoc();
        echo number_format($revenueResult['total'] ?? 0, 2);
        ?>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-receipt"></i></div>
      <div class="stat-label">Total Invoices</div>
      <div class="stat-value">
        <?php
        $invoiceCount = $conn->query("SELECT COUNT(*) as count FROM invoices")->fetch_assoc();
        echo $invoiceCount['count'];
        ?>
      </div>
    </div>
  </div>

  <div class="table-container">
    <div class="table-header">
      <h3>All Invoices</h3>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>Invoice ID</th>
            <th>Client</th>
            <th>Appointment</th>
            <th>Labor</th>
            <th>Parts</th>
            <th>Total</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($paymentsQuery && $paymentsQuery->num_rows > 0) {
            while ($invoice = $paymentsQuery->fetch_assoc()) {
              echo "<tr>
                <td>
                <td>{$invoice['full_name']}</td>
                <td>
                <td>\${$invoice['total_labor']}</td>
                <td>\${$invoice['total_parts']}</td>
                <td><strong>\${$invoice['grand_total']}</strong></td>
                <td>" . date('M d, Y', strtotime($invoice['invoice_date'])) . "</td>
                <td>
                  <div class='action-buttons'>
                    <button class='btn btn-primary btn-sm'>View</button>
                  </div>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='8' style='text-align: center; padding: 20px;'>No invoices found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

