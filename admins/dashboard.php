<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

include __DIR__ . '/../includes/adminHeader.php';
?>

<div class="admin-sidebar-shared">
  <div class="list-group">
    <a href="/vehicare_db/admins/dashboard.php" class="list-group-item active">
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
    <a href="/vehicare_db/admins/services.php" class="list-group-item">
      <i class="fas fa-cogs"></i> Services
    </a>
    <a href="/vehicare_db/admins/parts.php" class="list-group-item">
      <i class="fas fa-box"></i> Parts & Inventory
    </a>
    <a href="/vehicare_db/admins/staff.php" class="list-group-item">
      <i class="fas fa-people-group"></i> Staff
    </a>
    <a href="/vehicare_db/admins/payments.php" class="list-group-item">
      <i class="fas fa-money-bill"></i> Payments
    </a>
    <a href="/vehicare_db/admins/reports.php" class="list-group-item">
      <i class="fas fa-file-chart-line"></i> Reports
    </a>
  </div>
</div>

<div class="admin-main-content">
  <div style="margin-bottom: 30px;">
    <h1 style="color: #1a3a52; margin-bottom: 10px;">Dashboard</h1>
    <p style="color: #666;">Welcome back! Here's an overview of your VehiCare system.</p>
  </div>

  <!-- Statistics Cards -->
  <div class="dashboard-stats">
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-users"></i></div>
      <div class="stat-label">Total Clients</div>
      <div class="stat-value">
        <?php
        $clientCount = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc();
        echo $clientCount['count'];
        ?>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-car"></i></div>
      <div class="stat-label">Total Vehicles</div>
      <div class="stat-value">
        <?php
        $vehicleCount = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc();
        echo $vehicleCount['count'];
        ?>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><i class="fas fa-calendar"></i></div>
      <div class="stat-label">Pending Appointments</div>
      <div class="stat-value">
        <?php
        $appointmentCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'")->fetch_assoc();
        echo $appointmentCount['count'];
        ?>
      </div>
    </div>
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
  </div>

  <!-- Recent Appointments -->
  <div class="table-container" style="margin-bottom: 30px;">
    <div class="table-header">
      <h3>Recent Appointments</h3>
      <a href="/vehicare_db/admins/appointments.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>Appointment ID</th>
            <th>Client Name</th>
            <th>Vehicle</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $appointmentsQuery = $conn->query("
            SELECT a.*, c.full_name, v.plate_number, v.car_brand, v.car_model, s.service_name
            FROM appointments a
            LEFT JOIN clients c ON a.client_id = c.client_id
            LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
            LEFT JOIN services s ON a.service_id = s.service_id
            ORDER BY a.appointment_date DESC
            LIMIT 5
          ");
          
          if ($appointmentsQuery) {
            while ($apt = $appointmentsQuery->fetch_assoc()) {
              $statusBadge = 'badge-primary';
              if ($apt['status'] == 'Completed') $statusBadge = 'badge-success';
              if ($apt['status'] == 'Cancelled') $statusBadge = 'badge-danger';
              
              echo "<tr>
                <td>#{$apt['appointment_id']}</td>
                <td>{$apt['full_name']}</td>
                <td>{$apt['car_brand']} {$apt['car_model']}</td>
                <td>{$apt['service_name']}</td>
                <td>" . date('M d, Y', strtotime($apt['appointment_date'])) . "</td>
                <td>" . date('h:i A', strtotime($apt['appointment_time'])) . "</td>
                <td><span class='badge {$statusBadge}'>{$apt['status']}</span></td>
                <td>
                  <div class='action-buttons'>
                    <a href='/vehicare_db/admins/appointments.php?edit={$apt['appointment_id']}' class='btn btn-primary btn-sm'>Edit</a>
                    <a href='/vehicare_db/admins/delete.php?type=appointment&id={$apt['appointment_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                  </div>
                </td>
              </tr>";
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Invoices -->
  <div class="table-container">
    <div class="table-header">
      <h3>Recent Invoices</h3>
      <a href="/vehicare_db/admins/payments.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div style="overflow-x: auto;">
      <table class="table">
        <thead>
          <tr>
            <th>Invoice ID</th>
            <th>Appointment</th>
            <th>Labor Cost</th>
            <th>Parts Cost</th>
            <th>Total</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $invoicesQuery = $conn->query("
            SELECT i.*, a.appointment_id
            FROM invoices i
            LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
            ORDER BY i.invoice_date DESC
            LIMIT 5
          ");
          
          if ($invoicesQuery) {
            while ($inv = $invoicesQuery->fetch_assoc()) {
              echo "<tr>
                <td>#{$inv['invoice_id']}</td>
                <td>#{$inv['appointment_id']}</td>
                <td>\${$inv['total_labor']}</td>
                <td>\${$inv['total_parts']}</td>
                <td><strong>\${$inv['grand_total']}</strong></td>
                <td>" . date('M d, Y', strtotime($inv['invoice_date'])) . "</td>
              </tr>";
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
