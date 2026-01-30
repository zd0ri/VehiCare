<!-- Shared Admin Sidebar Navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-car"></i> VehiCare</h3>
        <small>Admin Panel</small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="/vehicare_db/admins/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-dashboard"></i><span>Dashboard</span></a></li>
        
        <li class="menu-section">Bookings</li>
        <li><a href="/vehicare_db/admins/appointments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>"><i class="fas fa-calendar"></i><span>Appointments</span></a></li>
        <li><a href="/vehicare_db/admins/walk_in_booking.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'walk_in_booking.php' ? 'active' : ''; ?>"><i class="fas fa-door-open"></i><span>Walk-In Bookings</span></a></li>
        
        <li class="menu-section">Management</li>
        <li><a href="/vehicare_db/admins/clients.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i><span>Clients</span></a></li>
        <li><a href="/vehicare_db/admins/vehicles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'vehicles.php' ? 'active' : ''; ?>"><i class="fas fa-car"></i><span>Vehicles</span></a></li>
        <li><a href="/vehicare_db/admins/technicians.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'technicians.php' ? 'active' : ''; ?>"><i class="fas fa-tools"></i><span>Technicians</span></a></li>
        <li><a href="/vehicare_db/admins/assignments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'assignments.php' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i><span>Assignments</span></a></li>
        
        <li class="menu-section">Operations</li>
        <li><a href="/vehicare_db/admins/queue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'queue.php' ? 'active' : ''; ?>"><i class="fas fa-list-ol"></i><span>Queue</span></a></li>
        <li><a href="/vehicare_db/admins/inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>"><i class="fas fa-boxes"></i><span>Inventory</span></a></li>
        <li><a href="/vehicare_db/admins/services.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>"><i class="fas fa-wrench"></i><span>Services</span></a></li>
        
        <li class="menu-section">Financial</li>
        <li><a href="/vehicare_db/admins/payments.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>"><i class="fas fa-credit-card"></i><span>Payments</span></a></li>
        <li><a href="/vehicare_db/admins/invoices.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>"><i class="fas fa-receipt"></i><span>Invoices</span></a></li>
        
        <li class="menu-section">Reports & System</li>
        <li><a href="/vehicare_db/admins/ratings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'ratings.php' ? 'active' : ''; ?>"><i class="fas fa-star"></i><span>Ratings</span></a></li>
        <li><a href="/vehicare_db/admins/notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>"><i class="fas fa-bell"></i><span>Notifications</span></a></li>
        <li><a href="/vehicare_db/admins/audit_logs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'audit_logs.php' ? 'active' : ''; ?>"><i class="fas fa-history"></i><span>Audit Logs</span></a></li>
        
        <li style="border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px; padding-top: 20px;">
            <a href="/vehicare_db/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </li>
    </ul>
</aside>
