<?php
/**
 * Authentication Helper Functions
 * Global helper functions for authentication and role checking
 */

if (!function_exists('checkAuth')) {
    /**
     * Check if user is authenticated
     */
    function checkAuth($redirect_to_login = true) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $authenticated = isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
        
        if (!$authenticated && $redirect_to_login) {
            header('Location: /vehicare_db/login.php');
            exit;
        }
        
        return $authenticated;
    }
}

if (!function_exists('checkRole')) {
    /**
     * Check if user has one of the allowed roles
     */
    function checkRole($allowedRoles, $redirect_to_403 = true) {
        if (!checkAuth()) {
            return false;
        }
        
        if (is_string($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        $userRole = $_SESSION['user_role'] ?? null;
        $hasRole = in_array($userRole, $allowedRoles);
        
        if (!$hasRole && $redirect_to_403) {
            header('Location: /vehicare_db/403.php');
            exit;
        }
        
        return $hasRole;
    }
}

if (!function_exists('getCurrentUser')) {
    /**
     * Get current user information
     */
    function getCurrentUser() {
        if (!checkAuth(false)) {
            return null;
        }
        
        // Try to get from session first
        if (isset($_SESSION['user_data'])) {
            return $_SESSION['user_data'];
        }
        
        // If not in session, get from database
        global $pdo;
        if ($pdo && isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("
                    SELECT user_id, email, full_name, phone, address, role, status, 
                           profile_picture, created_date, last_login
                    FROM users 
                    WHERE user_id = ? AND status = 'active'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Store in session for faster access
                    $_SESSION['user_data'] = $user;
                    return $user;
                }
            } catch (Exception $e) {
                error_log("Error getting current user: " . $e->getMessage());
            }
        }
        
        return null;
    }
}

if (!function_exists('hasPermission')) {
    /**
     * Check if user has specific permission
     */
    function hasPermission($permission) {
        $user = getCurrentUser();
        if (!$user) return false;
        
        $role = $user['role'];
        
        // Define role-based permissions
        $permissions = [
            'admin' => [
                'view_all_appointments', 'create_appointments', 'edit_appointments', 'delete_appointments',
                'assign_technicians', 'view_all_clients', 'edit_clients', 'delete_clients',
                'view_all_vehicles', 'edit_vehicles', 'delete_vehicles', 'view_all_invoices',
                'create_invoices', 'edit_invoices', 'delete_invoices', 'process_payments',
                'view_all_payments', 'manage_inventory', 'manage_services', 'manage_users',
                'view_reports', 'system_settings', 'audit_access', 'full_admin_access'
            ],
            'staff' => [
                'view_assigned_appointments', 'update_appointment_status', 'view_service_queue',
                'view_client_info', 'view_vehicle_info', 'create_ratings', 'view_own_ratings',
                'update_work_progress', 'access_parts_inventory'
            ],
            'client' => [
                'view_own_appointments', 'create_appointments', 'edit_own_appointments',
                'view_own_vehicles', 'edit_own_vehicles', 'view_own_invoices',
                'make_payments', 'view_own_ratings', 'create_ratings'
            ]
        ];
        
        $rolePermissions = $permissions[$role] ?? [];
        return in_array($permission, $rolePermissions);
    }
}

if (!function_exists('canAccessModule')) {
    /**
     * Check if user can access specific module
     */
    function canAccessModule($module) {
        $user = getCurrentUser();
        if (!$user) return false;
        
        $role = $user['role'];
        
        // Define module access by role
        $moduleAccess = [
            'admin' => [
                'dashboard', 'appointments', 'queue', 'walk_in_booking', 'assignments',
                'services', 'inventory', 'parts', 'clients', 'vehicles', 'invoices',
                'payments', 'staff', 'technicians', 'users', 'audit_logs', 'ratings',
                'notifications', 'profile'
            ],
            'staff' => [
                'dashboard', 'assignments', 'queue', 'ratings', 'notifications', 'profile'
            ],
            'client' => [
                'dashboard', 'appointments', 'vehicles', 'invoices', 'payments', 
                'ratings', 'profile'
            ]
        ];
        
        $allowedModules = $moduleAccess[$role] ?? [];
        return in_array($module, $allowedModules);
    }
}

if (!function_exists('requireRole')) {
    /**
     * Require specific role or redirect
     */
    function requireRole($allowedRoles) {
        return checkRole($allowedRoles, true);
    }
}

if (!function_exists('requirePermission')) {
    /**
     * Require specific permission or redirect to 403
     */
    function requirePermission($permission) {
        if (!hasPermission($permission)) {
            header('Location: /vehicare_db/403.php');
            exit;
        }
        return true;
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Check if current user is admin
     */
    function isAdmin() {
        return checkRole('admin', false);
    }
}

if (!function_exists('isStaff')) {
    /**
     * Check if current user is staff or admin
     */
    function isStaff() {
        return checkRole(['staff', 'admin'], false);
    }
}

if (!function_exists('isClient')) {
    /**
     * Check if current user is client
     */
    function isClient() {
        return checkRole('client', false);
    }
}

if (!function_exists('getUserRole')) {
    /**
     * Get current user role
     */
    function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

if (!function_exists('redirectTo403')) {
    /**
     * Redirect to 403 forbidden page
     */
    function redirectTo403() {
        header('HTTP/1.0 403 Forbidden');
        header('Location: /vehicare_db/403.php');
        exit;
    }
}
?>