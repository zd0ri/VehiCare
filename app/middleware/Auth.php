<?php
/**
 * Authentication Class
 * VehiCare Service Management System
 * 
 * Handles user authentication, session management, and security
 */

class Auth {
    private $pdo;
    private $session_timeout;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->session_timeout = SESSION_TIMEOUT;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check session timeout
        $this->checkSessionTimeout();
    }
    
    /**
     * User login
     */
    public function login($email, $password, $remember_me = false) {
        try {
            // Rate limiting check (simple implementation)
            if ($this->isRateLimited($email)) {
                return [
                    'success' => false,
                    'message' => 'Too many login attempts. Please try again later.'
                ];
            }
            
            // Get user from database
            $stmt = $this->pdo->prepare("
                SELECT user_id, email, password, full_name, role, status, last_login 
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->logFailedLogin($email, 'User not found');
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                $this->logFailedLogin($email, 'Invalid password');
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Create session
            $this->createSession($user);
            
            // Update last login
            $this->updateLastLogin($user['user_id']);
            
            // Handle remember me
            if ($remember_me) {
                $this->setRememberMeToken($user['user_id']);
            }
            
            // Log successful login
            log_event('INFO', "User logged in: {$email}", [
                'user_id' => $user['user_id'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            // Audit log
            $this->logAudit($user['user_id'], 'LOGIN', 'users', $user['user_id'], null, null, 'User logged in successfully');
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];
            
        } catch (Exception $e) {
            log_event('ERROR', "Login error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Login failed. Please try again.'
            ];
        }
    }
    
    /**
     * User logout
     */
    public function logout($user_id = null) {
        $current_user_id = $user_id ?? $_SESSION['user_id'] ?? null;
        
        if ($current_user_id) {
            // Log logout
            log_event('INFO', "User logged out", ['user_id' => $current_user_id]);
            
            // Audit log
            $this->logAudit($current_user_id, 'LOGOUT', 'users', $current_user_id, null, null, 'User logged out');
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Remove remember me token
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($required_role) {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $user_role = $_SESSION['role'] ?? null;
        
        // Admin has access to everything
        if ($user_role === 'admin') {
            return true;
        }
        
        // Check specific role
        if (is_array($required_role)) {
            return in_array($user_role, $required_role);
        }
        
        return $user_role === $required_role;
    }
    
    /**
     * Check if user can access specific resource
     */
    public function canAccess($resource, $action = 'view') {
        if (!$this->isAuthenticated()) {
            return false;
        }
        
        $user_role = $_SESSION['role'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null;
        
        // Role-based access control matrix
        $permissions = [
            'admin' => [
                'dashboard' => ['view', 'create', 'edit', 'delete'],
                'appointments' => ['view', 'create', 'edit', 'delete'],
                'clients' => ['view', 'create', 'edit', 'delete'],
                'vehicles' => ['view', 'create', 'edit', 'delete'],
                'technicians' => ['view', 'create', 'edit', 'delete'],
                'assignments' => ['view', 'create', 'edit', 'delete'],
                'queue' => ['view', 'create', 'edit', 'delete'],
                'inventory' => ['view', 'create', 'edit', 'delete'],
                'services' => ['view', 'create', 'edit', 'delete'],
                'payments' => ['view', 'create', 'edit', 'delete'],
                'invoices' => ['view', 'create', 'edit', 'delete'],
                'ratings' => ['view', 'edit', 'delete'],
                'notifications' => ['view', 'create', 'edit', 'delete'],
                'audit_logs' => ['view'],
                'settings' => ['view', 'edit']
            ],
            'staff' => [
                'dashboard' => ['view'],
                'appointments' => ['view', 'edit'], // Only assigned appointments
                'assignments' => ['view', 'edit'], // Only own assignments
                'queue' => ['view', 'edit'],
                'inventory' => ['view'],
                'services' => ['view'],
                'ratings' => ['view']
            ],
            'client' => [
                'dashboard' => ['view'],
                'appointments' => ['view', 'create'], // Only own appointments
                'vehicles' => ['view', 'create', 'edit'], // Only own vehicles
                'invoices' => ['view'], // Only own invoices
                'ratings' => ['view', 'create'] // Only own ratings
            ]
        ];
        
        $user_permissions = $permissions[$user_role] ?? [];
        $resource_permissions = $user_permissions[$resource] ?? [];
        
        return in_array($action, $resource_permissions);
    }
    
    /**
     * Get current user information
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role']
        ];
    }
    
    /**
     * Require authentication
     */
    public function requireAuth($redirect_url = '/vehicare_db/login.php') {
        if (!$this->isAuthenticated()) {
            header("Location: $redirect_url");
            exit;
        }
    }
    
    /**
     * Require specific role
     */
    public function requireRole($required_role, $redirect_url = '/vehicare_db/unauthorized.php') {
        $this->requireAuth();
        
        if (!$this->hasRole($required_role)) {
            header("Location: $redirect_url");
            exit;
        }
    }
    
    /**
     * Change user password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Get current password
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ?, updated_date = NOW() WHERE user_id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            // Log password change
            log_event('INFO', "Password changed for user ID: $user_id");
            $this->logAudit($user_id, 'PASSWORD_CHANGE', 'users', $user_id, null, null, 'Password changed');
            
            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
            
        } catch (Exception $e) {
            log_event('ERROR', "Password change error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to change password'
            ];
        }
    }
    
    /**
     * Private methods
     */
    
    private function createSession($user) {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_role'] = $user['role']; // For helper functions
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Store complete user data for helper functions
        $_SESSION['user_data'] = [
            'user_id' => $user['user_id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'phone' => $user['phone'] ?? null,
            'address' => $user['address'] ?? null,
            'role' => $user['role'],
            'status' => $user['status'],
            'profile_picture' => $user['profile_picture'] ?? null,
            'created_date' => $user['created_date'] ?? null,
            'last_login' => $user['last_login'] ?? null
        ];
    }
    
    private function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            if ((time() - $_SESSION['last_activity']) > $this->session_timeout) {
                $this->logout();
                return false;
            }
        }
        
        if (isset($_SESSION['authenticated'])) {
            $_SESSION['last_activity'] = time();
        }
        
        return true;
    }
    
    private function updateLastLogin($user_id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            log_event('ERROR', "Failed to update last login: " . $e->getMessage());
        }
    }
    
    private function isRateLimited($email) {
        // Simple rate limiting - in production, use Redis or database
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        $current_time = time();
        $window = 300; // 5 minutes
        $max_attempts = 5;
        
        // Clean old attempts
        $_SESSION['login_attempts'] = array_filter(
            $_SESSION['login_attempts'],
            function($timestamp) use ($current_time, $window) {
                return ($current_time - $timestamp) < $window;
            }
        );
        
        return count($_SESSION['login_attempts']) >= $max_attempts;
    }
    
    private function logFailedLogin($email, $reason) {
        $_SESSION['login_attempts'][] = time();
        
        log_event('WARNING', "Failed login attempt for: $email", [
            'reason' => $reason,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    private function setRememberMeToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + 86400 * 30; // 30 days
        
        // Store token in database (you might want to create a remember_tokens table)
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO user_sessions (session_id, user_id, expires_at) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE expires_at = ?
            ");
            $stmt->execute([$token, $user_id, date('Y-m-d H:i:s', $expiry), date('Y-m-d H:i:s', $expiry)]);
            
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
        } catch (Exception $e) {
            log_event('ERROR', "Failed to set remember me token: " . $e->getMessage());
        }
    }
    
    private function logAudit($user_id, $action, $table_name, $record_id, $old_values, $new_values, $description) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id,
                $action,
                $table_name,
                $record_id,
                $old_values ? json_encode($old_values) : null,
                $new_values ? json_encode($new_values) : null,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            log_event('ERROR', "Failed to log audit: " . $e->getMessage());
        }
    }
}
?>