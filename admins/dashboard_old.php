<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/index.php");
    exit;
}

// Get statistics
$clientCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'")->fetch_assoc()['count'];
$vehicleCount = $conn->query("SELECT COUNT(*) as count FROM vehicles")->fetch_assoc()['count'];
$appointmentCount = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'")->fetch_assoc()['count'];
$revenueResult = $conn->query("SELECT SUM(grand_total) as total FROM invoices")->fetch_assoc();
$totalRevenue = number_format($revenueResult['total'] ?? 0, 2);
$totalServices = $conn->query("SELECT COUNT(*) as count FROM services")->fetch_assoc()['count'];
$totalStaff = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'")->fetch_assoc()['count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VehiCare Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #0052cc 0%, #0052cc 100%);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            padding: 20px 0;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar-brand {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand i {
            font-size: 32px;
            color: #fff;
            display: block;
            margin-bottom: 10px;
        }

        .sidebar-brand h5 {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 16px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #ff6b6b;
        }

        .sidebar-menu i {
            font-size: 18px;
            width: 20px;
        }

        /* Main Content */
        .main-content {
            margin-left: 220px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .admin-header {
            background: #fff;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .header-left h2 {
            margin: 0;
            color: #333;
            font-weight: 700;
            font-size: 24px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-time {
            font-size: 14px;
            color: #666;
            font-weight: 500;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f0f0f0;
            border-radius: 20px;
            cursor: pointer;
        }

        .header-user i {
            width: 30px;
            height: 30px;
            background: #0052cc;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        /* Content Area */
        .content-area {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        /* Top Metrics Row */
        .metrics-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
        }

        .metric-card.red {
            border-top: 4px solid #ff6b6b;
        }

        .metric-card.blue {
            border-top: 4px solid #0052cc;
        }

        .metric-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .metric-change {
            font-size: 12px;
            color: #27ae60;
            margin-top: 8px;
        }

        /* Two Column Layout for Charts and Tables */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .dashboard-grid.full {
            grid-template-columns: 1fr;
        }

        /* Card Styles */
        .dashboard-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e0e0;
            padding: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            font-weight: 700;
            font-size: 16px;
        }

        .card-header .btn-link {
            color: #0052cc;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
        }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }

        /* Table Styles */
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dashboard-table thead th {
            background: #f5f7fa;
            padding: 12px;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .dashboard-table tbody td {
            padding: 14px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
            color: #333;
        }

        .dashboard-table tbody tr:hover {
            background: #f9f9f9;
        }

        /* Status Badge */
        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: absolute;
            }

            .main-content {
                margin-left: 0;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .metrics-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-car"></i>
                <h5>VehiCare</h5>
            </div>
            <ul class="sidebar-menu">
                <li><a href="/vehicare_db/admins/dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="/vehicare_db/admins/users.php"><i class="fas fa-users"></i> Users</a></li>
                <li><a href="/vehicare_db/admins/clients.php"><i class="fas fa-user-tie"></i> Clients</a></li>
                <li><a href="/vehicare_db/admins/vehicles.php"><i class="fas fa-car"></i> Vehicles</a></li>
                <li><a href="/vehicare_db/admins/appointments.php"><i class="fas fa-calendar"></i> Appointments</a></li>
                <li><a href="/vehicare_db/admins/services.php"><i class="fas fa-cogs"></i> Services</a></li>
                <li><a href="/vehicare_db/admins/staff.php"><i class="fas fa-people-group"></i> Staff</a></li>
                <li><a href="/vehicare_db/admins/parts.php"><i class="fas fa-box"></i> Parts</a></li>
                <li><a href="/vehicare_db/admins/payments.php"><i class="fas fa-money-bill"></i> Payments</a></li>
                <li><a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <h2>Dashboard</h2>
                </div>
                <div class="header-right">
                    <span class="header-time" id="current-time">09:55</span>
                    <div class="header-user">
                        <i class="fas fa-bell"></i>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content-area">
                <!-- Key Metrics -->
                <div class="metrics-row">
                    <div class="metric-card red">
                        <div class="metric-label">Total Revenue</div>
                        <div class="metric-value">$<?php echo $totalRevenue; ?></div>
                        <div class="metric-change">↑ 12.5% from last month</div>
                    </div>
                    <div class="metric-card blue">
                        <div class="metric-label">Total Clients</div>
                        <div class="metric-value"><?php echo $clientCount; ?></div>
                        <div class="metric-change">↑ 8 new this month</div>
                    </div>
                    <div class="metric-card red">
                        <div class="metric-label">Pending Appointments</div>
                        <div class="metric-value"><?php echo $appointmentCount; ?></div>
                        <div class="metric-change">Need attention</div>
                    </div>
                    <div class="metric-card blue">
                        <div class="metric-label">Total Vehicles</div>
                        <div class="metric-value"><?php echo $vehicleCount; ?></div>
                        <div class="metric-change">↑ 3 new vehicles</div>
                    </div>
                </div>

                <!-- Charts and Tables -->
                <div class="dashboard-grid">
                    <!-- Revenue Chart -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Revenue Trend</h3>
                            <a href="#" class="card-header-link">View Report</a>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Efficiency Chart -->
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3>Service Efficiency</h3>
                            <a href="#" class="card-header-link">Details</a>
                        </div>
                        <div class="chart-container">
                            <canvas id="efficiencyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Appointments</h3>
                        <a href="/vehicare_db/admins/appointments.php" class="card-header-link">View All</a>
                    </div>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $appointments = $conn->query("
                                SELECT a.*, c.full_name, s.service_name 
                                FROM appointments a
                                JOIN clients c ON a.client_id = c.client_id
                                JOIN services s ON a.service_id = s.service_id
                                ORDER BY a.appointment_date DESC LIMIT 5
                            ");
                            
                            if ($appointments && $appointments->num_rows > 0) {
                                while ($apt = $appointments->fetch_assoc()) {
                                    $status_class = strtolower($apt['status']);
                                    echo "
                                    <tr>
                                        <td>" . htmlspecialchars($apt['full_name']) . "</td>
                                        <td>" . htmlspecialchars($apt['service_name']) . "</td>
                                        <td>" . date('M d, Y', strtotime($apt['appointment_date'])) . "</td>
                                        <td>" . date('h:i A', strtotime($apt['appointment_time'])) . "</td>
                                        <td><span class='badge-" . ($apt['status'] == 'Completed' ? 'success' : ($apt['status'] == 'Pending' ? 'warning' : 'danger')) . "'>" . ucfirst($apt['status']) . "</span></td>
                                    </tr>
                                    ";
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align: center; color: #999;'>No appointments found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}`;
        }
        updateTime();
        setInterval(updateTime, 60000);

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Revenue',
                        data: [65000, 75000, 72000, 85000, 95000, 88000],
                        borderColor: '#0052cc',
                        backgroundColor: 'rgba(0, 82, 204, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#0052cc'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Efficiency Chart
        const efficiencyCtx = document.getElementById('efficiencyChart');
        if (efficiencyCtx) {
            new Chart(efficiencyCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending', 'Cancelled'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: ['#27ae60', '#ff6b6b', '#f39c12']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
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
