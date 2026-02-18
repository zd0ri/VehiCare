<?php
/**
 * Admin Dashboard - Main Entry Point
 * Displays statistics, quick actions, and recent activity
 */

// Page configuration
$page_title = 'Dashboard';
$page_icon = 'fas fa-tachometer-alt';
$breadcrumbs = [
    ['name' => 'Dashboard']
];

// Include the admin header
include_once __DIR__ . '/../app/views/layouts/admin_header.php';

// Include required models for dashboard data
require_once __DIR__ . '/../app/models/BaseModel.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Appointment.php';
require_once __DIR__ . '/../app/models/Vehicle.php';
require_once __DIR__ . '/../app/models/Service.php';

// Initialize models
$appointmentModel = new Appointment();
$userModel = new User();
$vehicleModel = new Vehicle();
$serviceModel = new Service();

// Get current date ranges for statistics
$today = date('Y-m-d');
$this_week_start = date('Y-m-d', strtotime('monday this week'));
$this_month_start = date('Y-m-01');
$last_month_start = date('Y-m-01', strtotime('-1 month'));
$last_month_end = date('Y-m-t', strtotime('-1 month'));

try {
    // Get dashboard statistics based on user role
    $stats = [];
    
    if ($rbac->hasPermission('view_all_appointments')) {
        // Admin/Manager stats - full access
        $stats['total_appointments'] = $appointmentModel->count();
        $stats['today_appointments'] = $appointmentModel->count(['appointment_date' => $today]);
        $stats['pending_appointments'] = $appointmentModel->count(['status' => 'pending']);
        $stats['confirmed_appointments'] = $appointmentModel->count(['status' => 'confirmed']);
        $stats['in_progress_appointments'] = $appointmentModel->count(['status' => 'in-progress']);
        $stats['completed_today'] = $appointmentModel->count(['status' => 'completed', 'appointment_date' => $today]);
        
        // Client and vehicle stats
        $stats['total_clients'] = $userModel->count(['role' => 'client']);
        $stats['total_vehicles'] = $vehicleModel->count();
        $stats['active_services'] = $serviceModel->count(['is_active' => 1]);
        
        // Revenue stats (if accessible)
        $stats['month_revenue'] = 0; // TODO: Calculate from invoices
        $stats['pending_payments'] = 0; // TODO: Calculate from payments table
        
    } else {
        // Staff stats - limited to assigned work
        $user_id = $current_user['id'];
        $stats['my_assignments'] = $appointmentModel->count(['assigned_technician' => $user_id]);
        $stats['my_pending'] = $appointmentModel->count(['assigned_technician' => $user_id, 'status' => 'pending']);
        $stats['my_in_progress'] = $appointmentModel->count(['assigned_technician' => $user_id, 'status' => 'in-progress']);
        $stats['my_completed_today'] = $appointmentModel->count([
            'assigned_technician' => $user_id, 
            'status' => 'completed', 
            'appointment_date' => $today
        ]);
    }
    
    // Recent appointments (role-based)
    $recent_appointments_conditions = [];
    if (!$rbac->hasPermission('view_all_appointments')) {
        $recent_appointments_conditions['assigned_technician'] = $current_user['id'];
    }
    
    $recent_appointments = $appointmentModel->findAll(
        $recent_appointments_conditions,
        ['appointment_date' => 'DESC', 'appointment_time' => 'DESC'],
        10
    );
    
    // Get appointment details with related data
    foreach ($recent_appointments as &$appointment) {
        $client = $userModel->find($appointment['client_id']);
        $vehicle = $vehicleModel->find($appointment['vehicle_id']);
        $service = $serviceModel->find($appointment['service_id']);
        
        $appointment['client_name'] = $client ? $client['first_name'] . ' ' . $client['last_name'] : 'Unknown';
        $appointment['vehicle_info'] = $vehicle ? $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] : 'Unknown Vehicle';
        $appointment['service_name'] = $service ? $service['name'] : 'Unknown Service';
    }
    
    // Quick stats for percentage changes (compare to last month)
    $last_month_appointments = $appointmentModel->count([
        'appointment_date >=' => $last_month_start,
        'appointment_date <=' => $last_month_end
    ]);
    
    $this_month_appointments = $appointmentModel->count([
        'appointment_date >=' => $this_month_start
    ]);
    
    $appointments_change = $last_month_appointments > 0 
        ? (($this_month_appointments - $last_month_appointments) / $last_month_appointments) * 100 
        : 0;

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $stats = [];
    $recent_appointments = [];
    $appointments_change = 0;
}
?>

<!-- Dashboard Content -->
<div class="dashboard-content">
    <!-- Welcome Section -->
    <div class="welcome-section mb-30">
        <div class="welcome-card">
            <div class="welcome-content">
                <h2>Welcome back, <?php echo htmlspecialchars($current_user['first_name']); ?>!</h2>
                <p>Here's what's happening with your <?php echo $rbac->hasRole('admin') ? 'business' : 'assignments'; ?> today.</p>
            </div>
            <div class="welcome-actions">
                <?php if ($rbac->hasPermission('create_appointments')): ?>
                <a href="/vehicare_db/admins/walk_in_booking.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    New Walk-in
                </a>
                <?php endif; ?>
                <a href="/vehicare_db/admins/appointments.php" class="btn btn-outline-primary">
                    <i class="fas fa-calendar"></i>
                    View Schedule
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <?php if ($rbac->hasPermission('view_all_appointments')): ?>
            <!-- Admin/Manager Dashboard -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Total Appointments</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-card-value" data-stat="total_appointments"><?php echo number_format($stats['total_appointments'] ?? 0); ?></div>
                <div class="stat-card-change <?php echo $appointments_change >= 0 ? '' : 'negative'; ?>">
                    <i class="fas fa-<?php echo $appointments_change >= 0 ? 'arrow-up' : 'arrow-down'; ?>"></i>
                    <?php echo abs(round($appointments_change, 1)); ?>% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Today's Appointments</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                <div class="stat-card-value" data-stat="today_appointments"><?php echo number_format($stats['today_appointments'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-clock"></i>
                    <?php echo ($stats['completed_today'] ?? 0); ?> completed
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Pending Appointments</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-card-value" data-stat="pending_appointments"><?php echo number_format($stats['pending_appointments'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-check-circle"></i>
                    <?php echo ($stats['confirmed_appointments'] ?? 0); ?> confirmed
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Active Clients</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #27ae60, #229954);">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card-value" data-stat="total_clients"><?php echo number_format($stats['total_clients'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-car"></i>
                    <?php echo ($stats['total_vehicles'] ?? 0); ?> vehicles
                </div>
            </div>

        <?php else: ?>
            <!-- Staff Dashboard -->
            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">My Assignments</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['my_assignments'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-clock"></i>
                    Total assignments
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Pending Work</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['my_pending'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-wrench"></i>
                    Waiting to start
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">In Progress</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-cogs"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['my_in_progress'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-play-circle"></i>
                    Currently working
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-header">
                    <h3 class="stat-card-title">Completed Today</h3>
                    <div class="stat-card-icon" style="background: linear-gradient(135deg, #27ae60, #229954);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card-value"><?php echo number_format($stats['my_completed_today'] ?? 0); ?></div>
                <div class="stat-card-change">
                    <i class="fas fa-trophy"></i>
                    Great work!
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Activity -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-history"></i>
                Recent Appointments
            </h2>
            <div class="card-actions">
                <a href="/vehicare_db/admins/appointments.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye"></i>
                    View All
                </a>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($recent_appointments)): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Vehicle</th>
                                <th>Service</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_appointments as $appointment): ?>
                            <tr data-id="<?php echo $appointment['id']; ?>">
                                <td>
                                    <div class="client-info">
                                        <strong><?php echo htmlspecialchars($appointment['client_name']); ?></strong>
                                        <?php if (isset($appointment['phone'])): ?>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="vehicle-info">
                                        <?php echo htmlspecialchars($appointment['vehicle_info']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="service-name"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                                </td>
                                <td>
                                    <div class="datetime-info">
                                        <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong>
                                        <small class="text-muted d-block"><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $appointment['status'] === 'completed' ? 'success' : 
                                            ($appointment['status'] === 'confirmed' ? 'info' : 
                                            ($appointment['status'] === 'in-progress' ? 'primary' : 
                                            ($appointment['status'] === 'cancelled' ? 'danger' : 'warning')));
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($appointment['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="/vehicare_db/admins/appointments.php?view=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" data-tooltip="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($appointment['status'] !== 'completed' && $appointment['status'] !== 'cancelled'): ?>
                                            <?php if ($rbac->hasPermission('edit_appointments')): ?>
                                            <button class="btn btn-sm btn-outline-secondary update-status-btn" 
                                                    data-id="<?php echo $appointment['id']; ?>" 
                                                    data-status="in-progress" 
                                                    data-type="appointment"
                                                    data-tooltip="Start Service">
                                                <i class="fas fa-play"></i>
                                            </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>No Recent Appointments</h3>
                    <p>No appointments found for your current role and permissions.</p>
                    <?php if ($rbac->hasPermission('create_appointments')): ?>
                    <a href="/vehicare_db/admins/walk_in_booking.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Create First Appointment
                    </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Charts Section -->
    <?php if ($rbac->hasPermission('view_all_appointments')): ?>
    <div class="charts-grid">
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-chart-pie"></i>
                    Appointments by Status
                </h2>
            </div>
            <div class="card-body">
                <canvas id="statusChart" width="400" height="300"></canvas>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    Monthly Trend
                </h2>
            </div>
            <div class="card-body">
                <canvas id="trendChart" width="400" height="300"></canvas>
            </div>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-chart-bar"></i>
                    Popular Services
                </h2>
            </div>
            <div class="card-body">
                <canvas id="servicesChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions Panel -->
    <div class="content-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>
        </div>
        <div class="card-body">
            <div class="quick-actions-grid">
                <?php if ($rbac->hasPermission('create_appointments')): ?>
                <a href="/vehicare_db/admins/walk_in_booking.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #3498db;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Walk-in Booking</h4>
                        <p>Add new appointment for walk-in customer</p>
                    </div>
                </a>
                <?php endif; ?>

                <a href="/vehicare_db/admins/queue.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #e74c3c;">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Service Queue</h4>
                        <p>View and manage service queue</p>
                    </div>
                </a>

                <?php if ($rbac->hasPermission('view_all_clients')): ?>
                <a href="/vehicare_db/admins/clients.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #27ae60;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Client Management</h4>
                        <p>Add or edit client information</p>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($rbac->hasPermission('manage_inventory')): ?>
                <a href="/vehicare_db/admins/inventory.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #f39c12;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Inventory</h4>
                        <p>Check parts and supplies</p>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($rbac->hasPermission('generate_reports')): ?>
                <a href="/vehicare_db/admins/reports.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #9b59b6;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Reports</h4>
                        <p>Generate business reports</p>
                    </div>
                </a>
                <?php endif; ?>

                <a href="/vehicare_db/admins/notifications.php" class="quick-action-item">
                    <div class="quick-action-icon" style="background: #e67e22;">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="quick-action-content">
                        <h4>Notifications</h4>
                        <p>View all notifications and alerts</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Dashboard-specific styles */
.welcome-card {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: var(--white);
    padding: 30px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 30px;
}

.welcome-content h2 {
    font-size: var(--font-size-2xl);
    font-weight: 600;
    margin: 0 0 8px;
}

.welcome-content p {
    margin: 0;
    opacity: 0.9;
}

.welcome-actions {
    display: flex;
    gap: 15px;
    flex-shrink: 0;
}

.welcome-actions .btn {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    color: var(--white);
}

.welcome-actions .btn:hover {
    background: rgba(255, 255, 255, 0.3);
    color: var(--white);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.quick-action-item {
    display: flex;
    align-items: center;
    padding: 20px;
    background: var(--white);
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
}

.quick-action-item:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--box-shadow-hover);
    text-decoration: none;
    color: inherit;
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.3rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.quick-action-content h4 {
    font-size: var(--font-size-base);
    font-weight: 600;
    margin: 0 0 5px;
    color: var(--gray-800);
}

.quick-action-content p {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
}

.empty-state-icon {
    font-size: 4rem;
    color: var(--gray-300);
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: var(--font-size-xl);
    color: var(--gray-600);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--gray-500);
    margin-bottom: 25px;
}

.client-info, .vehicle-info, .datetime-info {
    line-height: 1.4;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-container {
    position: relative;
    height: 300px;
}

@media (max-width: 768px) {
    .welcome-card {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .welcome-actions {
        justify-content: center;
    }
    
    .quick-actions-grid {
        grid-template-columns: 1fr;
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Add dashboard-specific class to body
document.body.classList.add('dashboard-page');
</script>

<?php
// Additional JavaScript for dashboard
$additional_js = [
    '/vehicare_db/assets/js/dashboard-charts.js'
];

// Include the admin footer
include_once __DIR__ . '/../app/views/layouts/admin_footer.php';
?>
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: #a01030;
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
            background: #dc143c;
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
            border-top: 4px solid #dc143c;
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
            color: #dc143c;
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
                        borderColor: '#dc143c',
                        backgroundColor: 'rgba(0, 82, 204, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#dc143c'
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
