<?php
/**
 * Session Middleware
 * VehiCare Service Management System
 * 
 * Handles session management and authentication checks
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Auth.php';

class SessionMiddleware {
    private $auth;
    
    public function __construct() {
        global $pdo;
        $this->auth = new Auth($pdo);
    }
    
    /**
     * Initialize session and authentication
     */
    public function init() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check for remember me token
        $this->checkRememberMe();
        
        return $this->auth;
    }
    
    /**
     * Require authentication for current page
     */
    public function requireAuth($redirect_url = null) {
        $auth = $this->init();
        
        if (!$redirect_url) {
            $redirect_url = $this->getLoginUrl();
        }
        
        $auth->requireAuth($redirect_url);
        return $auth;
    }
    
    /**
     * Require specific role for current page
     */
    public function requireRole($required_role, $unauthorized_url = null) {
        $auth = $this->init();
        
        if (!$unauthorized_url) {
            $unauthorized_url = $this->getUnauthorizedUrl();
        }
        
        $auth->requireRole($required_role, $unauthorized_url);
        return $auth;
    }
    
    /**
     * Require admin access
     */
    public function requireAdmin() {
        return $this->requireRole('admin');
    }
    
    /**
     * Require staff access (admin or staff)
     */
    public function requireStaff() {
        return $this->requireRole(['admin', 'staff']);
    }
    
    /**
     * Get authentication instance
     */
    public function getAuth() {
        return $this->init();
    }
    
    /**
     * Private methods
     */
    
    private function checkRememberMe() {
        if (!$this->auth->isAuthenticated() && isset($_COOKIE['remember_token'])) {
            $this->loginFromRememberToken($_COOKIE['remember_token']);
        }
    }
    
    private function loginFromRememberToken($token) {
        try {
            global $pdo;
            
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.email, u.full_name, u.role, u.status 
                FROM user_sessions s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.session_id = ? AND s.expires_at > NOW() AND u.status = 'active'
            ");
            $stmt->execute([$token]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Create new session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['authenticated'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                log_event('INFO', "User logged in via remember token: {$user['email']}", [
                    'user_id' => $user['user_id']
                ]);
            }
        } catch (Exception $e) {
            log_event('ERROR', "Remember me login error: " . $e->getMessage());
        }
    }
    
    private function getLoginUrl() {
        return '/vehicare_db/login.php';
    }
    
    private function getUnauthorizedUrl() {
        return '/vehicare_db/unauthorized.php';
    }
}

/**
 * Global helper functions for authentication
 */

/**
 * Quick authentication check
 */
function auth() {
    static $middleware = null;
    if ($middleware === null) {
        $middleware = new SessionMiddleware();
    }
    return $middleware->getAuth();
}

/**
 * Require authentication
 */
function require_auth() {
    $middleware = new SessionMiddleware();
    return $middleware->requireAuth();
}

/**
 * Require admin role
 */
function require_admin() {
    $middleware = new SessionMiddleware();
    return $middleware->requireAdmin();
}

/**
 * Require staff role
 */
function require_staff() {
    $middleware = new SessionMiddleware();
    return $middleware->requireStaff();
}

/**
 * Check if user can access resource
 */
function can_access($resource, $action = 'view') {
    return auth()->canAccess($resource, $action);
}

/**
 * Get current user
 */
function current_user() {
    return auth()->getCurrentUser();
}

/**
 * Check if user is authenticated
 */
function is_authenticated() {
    return auth()->isAuthenticated();
}

/**
 * Check if user has role
 */
function has_role($role) {
    return auth()->hasRole($role);
}
?>