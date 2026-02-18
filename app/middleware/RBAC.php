<?php
/**
 * Role-Based Access Control (RBAC) System
 * VehiCare Service Management System
 * 
 * Handles user roles, permissions, and access control
 */

class RBAC {
    private $pdo;
    
    // Define system roles
    const ROLES = [
        'admin' => 'Administrator',
        'staff' => 'Technician/Staff',
        'client' => 'Customer/Client'
    ];
    
    // Define system permissions
    const PERMISSIONS = [
        // Dashboard permissions
        'dashboard.view' => 'View Dashboard',
        'dashboard.stats' => 'View Dashboard Statistics',
        
        // User management
        'users.view' => 'View Users',
        'users.create' => 'Create Users', 
        'users.edit' => 'Edit Users',
        'users.delete' => 'Delete Users',
        
        // Client management
        'clients.view' => 'View Clients',
        'clients.create' => 'Create Clients',
        'clients.edit' => 'Edit Clients', 
        'clients.delete' => 'Delete Clients',
        
        // Vehicle management
        'vehicles.view' => 'View Vehicles',
        'vehicles.create' => 'Create Vehicles',
        'vehicles.edit' => 'Edit Vehicles',
        'vehicles.delete' => 'Delete Vehicles',
        'vehicles.own_only' => 'View Only Own Vehicles',
        
        // Appointment management
        'appointments.view' => 'View Appointments',
        'appointments.create' => 'Create Appointments',
        'appointments.edit' => 'Edit Appointments',
        'appointments.delete' => 'Delete Appointments',
        'appointments.assign' => 'Assign Appointments',
        'appointments.own_only' => 'View Only Own Appointments',
        'appointments.assigned_only' => 'View Only Assigned Appointments',
        
        // Technician management
        'technicians.view' => 'View Technicians',
        'technicians.create' => 'Create Technicians',
        'technicians.edit' => 'Edit Technicians',
        'technicians.delete' => 'Delete Technicians',
        
        // Assignment management
        'assignments.view' => 'View Assignments',
        'assignments.create' => 'Create Assignments',
        'assignments.edit' => 'Edit Assignments',
        'assignments.delete' => 'Delete Assignments',
        'assignments.own_only' => 'View Only Own Assignments',
        
        // Queue management
        'queue.view' => 'View Queue',
        'queue.manage' => 'Manage Queue',
        'queue.call_next' => 'Call Next Customer',
        
        // Inventory management
        'inventory.view' => 'View Inventory',
        'inventory.create' => 'Add Inventory Items',
        'inventory.edit' => 'Edit Inventory Items',
        'inventory.delete' => 'Delete Inventory Items',
        'inventory.adjust' => 'Adjust Stock Levels',
        
        // Service management
        'services.view' => 'View Services',
        'services.create' => 'Create Services',
        'services.edit' => 'Edit Services',
        'services.delete' => 'Delete Services',
        
        // Payment & Invoice management
        'payments.view' => 'View Payments',
        'payments.create' => 'Process Payments',
        'payments.edit' => 'Edit Payments',
        'payments.delete' => 'Delete Payments',
        'payments.own_only' => 'View Only Own Payments',
        
        'invoices.view' => 'View Invoices',
        'invoices.create' => 'Create Invoices',
        'invoices.edit' => 'Edit Invoices',
        'invoices.delete' => 'Delete Invoices',
        'invoices.send' => 'Send Invoices',
        'invoices.own_only' => 'View Only Own Invoices',
        
        // Rating & Review management
        'ratings.view' => 'View Ratings',
        'ratings.create' => 'Create Ratings',
        'ratings.edit' => 'Edit Ratings',
        'ratings.delete' => 'Delete Ratings',
        'ratings.moderate' => 'Moderate Ratings',
        'ratings.own_only' => 'View Only Own Ratings',
        
        // Notification management
        'notifications.view' => 'View Notifications',
        'notifications.create' => 'Create Notifications',
        'notifications.send' => 'Send Notifications',
        'notifications.delete' => 'Delete Notifications',
        
        // Audit & System management
        'audit_logs.view' => 'View Audit Logs',
        'system.settings' => 'Manage System Settings',
        'system.backup' => 'Backup System',
        'system.maintenance' => 'System Maintenance Mode',
        
        // Reports
        'reports.view' => 'View Reports',
        'reports.export' => 'Export Reports',
        'reports.financial' => 'Financial Reports',
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get role permissions matrix
     */
    public function getRolePermissions() {
        return [
            'admin' => [
                // Dashboard
                'dashboard.view', 'dashboard.stats',
                
                // Full user management
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
                'technicians.view', 'technicians.create', 'technicians.edit', 'technicians.delete',
                
                // Full vehicle management
                'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.delete',
                
                // Full appointment management
                'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete', 'appointments.assign',
                
                // Full assignment management
                'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete',
                
                // Full queue management
                'queue.view', 'queue.manage', 'queue.call_next',
                
                // Full inventory management
                'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete', 'inventory.adjust',
                
                // Full service management
                'services.view', 'services.create', 'services.edit', 'services.delete',
                
                // Full financial management
                'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
                'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete', 'invoices.send',
                
                // Rating management
                'ratings.view', 'ratings.edit', 'ratings.delete', 'ratings.moderate',
                
                // Notification management
                'notifications.view', 'notifications.create', 'notifications.send', 'notifications.delete',
                
                // System management
                'audit_logs.view', 'system.settings', 'system.backup', 'system.maintenance',
                
                // Reports
                'reports.view', 'reports.export', 'reports.financial',
            ],
            
            'staff' => [
                // Limited dashboard access
                'dashboard.view',
                
                // View clients and vehicles (for service)
                'clients.view', 'vehicles.view',
                
                // Limited appointment management 
                'appointments.view', 'appointments.edit', 'appointments.assigned_only',
                
                // Own assignment management
                'assignments.view', 'assignments.edit', 'assignments.own_only',
                
                // Queue management
                'queue.view', 'queue.manage', 'queue.call_next',
                
                // View inventory
                'inventory.view',
                
                // View services
                'services.view',
                
                // View ratings
                'ratings.view',
                
                // Basic notifications
                'notifications.view',
            ],
            
            'client' => [
                // Basic dashboard
                'dashboard.view',
                
                // Own vehicle management
                'vehicles.view', 'vehicles.create', 'vehicles.edit', 'vehicles.own_only',
                
                // Own appointment management
                'appointments.view', 'appointments.create', 'appointments.own_only',
                
                // View services
                'services.view',
                
                // Own financial records
                'payments.view', 'payments.own_only',
                'invoices.view', 'invoices.own_only',
                
                // Own ratings
                'ratings.view', 'ratings.create', 'ratings.own_only',
                
                // Own notifications
                'notifications.view',
            ]
        ];
    }
    
    /**
     * Check if user has permission
     */
    public function hasPermission($user_role, $permission, $user_id = null, $resource_owner_id = null) {
        $role_permissions = $this->getRolePermissions();
        $user_permissions = $role_permissions[$user_role] ?? [];
        
        // Check if user has the base permission
        if (!in_array($permission, $user_permissions)) {
            return false;
        }
        
        // Handle ownership-based permissions
        if ($resource_owner_id !== null && $user_id !== null) {
            if (str_ends_with($permission, '.own_only') && $user_id !== $resource_owner_id) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if user can access specific resource
     */
    public function canAccess($user_role, $resource, $action = 'view', $user_id = null, $resource_owner_id = null) {
        $permission = $resource . '.' . $action;
        return $this->hasPermission($user_role, $permission, $user_id, $resource_owner_id);
    }
    
    /**
     * Get accessible menu items for user role
     */
    public function getAccessibleMenuItems($user_role) {
        $menu_items = [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'url' => '/vehicare_db/app/views/' . $user_role . '/dashboard.php',
                'permission' => 'dashboard.view'
            ],
            'appointments' => [
                'title' => 'Appointments',
                'icon' => 'fas fa-calendar-check',
                'url' => '/vehicare_db/app/views/' . $user_role . '/appointments.php',
                'permission' => 'appointments.view'
            ],
            'walk_in' => [
                'title' => 'Walk-in Bookings',
                'icon' => 'fas fa-door-open',
                'url' => '/vehicare_db/app/views/admin/walk_in_booking.php',
                'permission' => 'queue.view',
                'roles' => ['admin', 'staff']
            ],
            'clients' => [
                'title' => 'Clients',
                'icon' => 'fas fa-users',
                'url' => '/vehicare_db/app/views/admin/clients.php',
                'permission' => 'clients.view',
                'roles' => ['admin', 'staff']
            ],
            'vehicles' => [
                'title' => 'Vehicles',
                'icon' => 'fas fa-car',
                'url' => '/vehicare_db/app/views/' . $user_role . '/vehicles.php',
                'permission' => 'vehicles.view'
            ],
            'technicians' => [
                'title' => 'Technicians',
                'icon' => 'fas fa-tools',
                'url' => '/vehicare_db/app/views/admin/technicians.php',
                'permission' => 'technicians.view',
                'roles' => ['admin']
            ],
            'assignments' => [
                'title' => 'Assignments',
                'icon' => 'fas fa-tasks',
                'url' => '/vehicare_db/app/views/' . $user_role . '/assignments.php',
                'permission' => 'assignments.view',
                'roles' => ['admin', 'staff']
            ],
            'queue' => [
                'title' => 'Service Queue',
                'icon' => 'fas fa-list-ol',
                'url' => '/vehicare_db/app/views/admin/queue.php',
                'permission' => 'queue.view',
                'roles' => ['admin', 'staff']
            ],
            'inventory' => [
                'title' => 'Inventory',
                'icon' => 'fas fa-boxes',
                'url' => '/vehicare_db/app/views/admin/inventory.php',
                'permission' => 'inventory.view',
                'roles' => ['admin']
            ],
            'services' => [
                'title' => 'Services',
                'icon' => 'fas fa-wrench',
                'url' => '/vehicare_db/app/views/admin/services.php',
                'permission' => 'services.view'
            ],
            'payments' => [
                'title' => 'Payments',
                'icon' => 'fas fa-credit-card',
                'url' => '/vehicare_db/app/views/' . $user_role . '/payments.php',
                'permission' => 'payments.view'
            ],
            'invoices' => [
                'title' => 'Invoices',
                'icon' => 'fas fa-receipt',
                'url' => '/vehicare_db/app/views/' . $user_role . '/invoices.php',
                'permission' => 'invoices.view'
            ],
            'ratings' => [
                'title' => 'Ratings & Reviews',
                'icon' => 'fas fa-star',
                'url' => '/vehicare_db/app/views/' . $user_role . '/ratings.php',
                'permission' => 'ratings.view'
            ],
            'notifications' => [
                'title' => 'Notifications',
                'icon' => 'fas fa-bell',
                'url' => '/vehicare_db/app/views/' . $user_role . '/notifications.php',
                'permission' => 'notifications.view'
            ],
            'audit_logs' => [
                'title' => 'Audit Logs',
                'icon' => 'fas fa-history',
                'url' => '/vehicare_db/app/views/admin/audit_logs.php',
                'permission' => 'audit_logs.view',
                'roles' => ['admin']
            ]
        ];
        
        $accessible_items = [];
        $role_permissions = $this->getRolePermissions()[$user_role] ?? [];
        
        foreach ($menu_items as $key => $item) {
            // Check if role is explicitly allowed
            if (isset($item['roles']) && !in_array($user_role, $item['roles'])) {
                continue;
            }
            
            // Check if user has required permission
            if (in_array($item['permission'], $role_permissions)) {
                $accessible_items[$key] = $item;
            }
        }
        
        return $accessible_items;
    }
    
    /**
     * Get dashboard widgets for user role
     */
    public function getDashboardWidgets($user_role) {
        $all_widgets = [
            'appointments_today' => [
                'title' => 'Today\'s Appointments',
                'permission' => 'appointments.view',
                'roles' => ['admin', 'staff', 'client']
            ],
            'total_clients' => [
                'title' => 'Total Clients',
                'permission' => 'clients.view',
                'roles' => ['admin']
            ],
            'active_technicians' => [
                'title' => 'Active Technicians',
                'permission' => 'technicians.view',
                'roles' => ['admin']
            ],
            'pending_invoices' => [
                'title' => 'Pending Invoices',
                'permission' => 'invoices.view',
                'roles' => ['admin', 'client']
            ],
            'monthly_revenue' => [
                'title' => 'Monthly Revenue',
                'permission' => 'reports.financial',
                'roles' => ['admin']
            ],
            'queue_status' => [
                'title' => 'Service Queue',
                'permission' => 'queue.view',
                'roles' => ['admin', 'staff']
            ],
            'my_assignments' => [
                'title' => 'My Assignments',
                'permission' => 'assignments.view',
                'roles' => ['staff']
            ],
            'my_vehicles' => [
                'title' => 'My Vehicles',
                'permission' => 'vehicles.view',
                'roles' => ['client']
            ],
            'recent_notifications' => [
                'title' => 'Recent Notifications',
                'permission' => 'notifications.view',
                'roles' => ['admin', 'staff', 'client']
            ],
            'low_inventory' => [
                'title' => 'Low Inventory Alert',
                'permission' => 'inventory.view',
                'roles' => ['admin']
            ]
        ];
        
        $accessible_widgets = [];
        $role_permissions = $this->getRolePermissions()[$user_role] ?? [];
        
        foreach ($all_widgets as $key => $widget) {
            // Check if role is explicitly allowed
            if (!in_array($user_role, $widget['roles'])) {
                continue;
            }
            
            // Check if user has required permission
            if (in_array($widget['permission'], $role_permissions)) {
                $accessible_widgets[$key] = $widget;
            }
        }
        
        return $accessible_widgets;
    }
    
    /**
     * Filter query results based on user permissions
     */
    public function applyAccessControl($query, $table, $user_role, $user_id) {
        switch ($table) {
            case 'appointments':
                if ($user_role === 'client') {
                    $query .= " AND client_id = ?";
                } elseif ($user_role === 'staff') {
                    $query .= " AND technician_id = ?";
                }
                break;
                
            case 'vehicles':
                if ($user_role === 'client') {
                    $query .= " AND client_id = ?";
                }
                break;
                
            case 'invoices':
                if ($user_role === 'client') {
                    $query .= " AND client_id = ?";
                }
                break;
                
            case 'assignments':
                if ($user_role === 'staff') {
                    $query .= " AND technician_id = ?";
                }
                break;
        }
        
        return $query;
    }
}
?>