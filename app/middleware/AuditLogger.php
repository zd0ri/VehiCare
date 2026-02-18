<?php
/**
 * Audit Logger Class
 * Tracks all user actions and database changes for compliance and security
 */

class AuditLogger {
    private $pdo;
    private $current_user_id;
    private $enabled;
    
    // Action types
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';
    const ACTION_CREATE = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_VIEW = 'VIEW';
    const ACTION_EXPORT = 'EXPORT';
    const ACTION_ACCESS_DENIED = 'ACCESS_DENIED';
    const ACTION_SETTINGS_CHANGE = 'SETTINGS_CHANGE';
    
    public function __construct($pdo = null) {
        global $pdo as $global_pdo;
        $this->pdo = $pdo ?? $global_pdo;
        $this->enabled = true;
        
        // Get current user ID from session
        if (isset($_SESSION['user_id'])) {
            $this->current_user_id = $_SESSION['user_id'];
        }
    }
    
    /**
     * Log an audit event
     */
    public function log($action, $table_name, $record_id = null, $old_values = null, $new_values = null, $description = null) {
        if (!$this->enabled || !$this->pdo) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO audit_logs (
                    user_id, action, table_name, record_id, old_values, new_values, 
                    description, ip_address, user_agent, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            return $stmt->execute([
                $this->current_user_id,
                $action,
                $table_name,
                $record_id,
                $old_values ? json_encode($old_values) : null,
                $new_values ? json_encode($new_values) : null,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
            
        } catch (Exception $e) {
            error_log("Audit log failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user login
     */
    public function logLogin($user_id, $success = true, $details = null) {
        $description = $success ? 'User logged in successfully' : 'Login attempt failed';
        if ($details) {
            $description .= ': ' . $details;
        }
        
        return $this->log(
            self::ACTION_LOGIN,
            'users',
            $user_id,
            null,
            ['success' => $success, 'timestamp' => date('Y-m-d H:i:s')],
            $description
        );
    }
    
    /**
     * Log user logout
     */
    public function logLogout($user_id) {
        return $this->log(
            self::ACTION_LOGOUT,
            'users',
            $user_id,
            null,
            ['timestamp' => date('Y-m-d H:i:s')],
            'User logged out'
        );
    }
    
    /**
     * Log record creation
     */
    public function logCreate($table_name, $record_id, $new_values, $description = null) {
        $description = $description ?? "New {$table_name} record created";
        
        return $this->log(
            self::ACTION_CREATE,
            $table_name,
            $record_id,
            null,
            $new_values,
            $description
        );
    }
    
    /**
     * Log record update
     */
    public function logUpdate($table_name, $record_id, $old_values, $new_values, $description = null) {
        $description = $description ?? "Record updated in {$table_name}";
        
        // Only log changed fields
        $changes = $this->getChangedFields($old_values, $new_values);
        if (empty($changes)) {
            return true; // No changes to log
        }
        
        return $this->log(
            self::ACTION_UPDATE,
            $table_name,
            $record_id,
            $old_values,
            $new_values,
            $description . ' - Fields changed: ' . implode(', ', array_keys($changes))
        );
    }
    
    /**
     * Log record deletion
     */
    public function logDelete($table_name, $record_id, $old_values, $description = null) {
        $description = $description ?? "Record deleted from {$table_name}";
        
        return $this->log(
            self::ACTION_DELETE,
            $table_name,
            $record_id,
            $old_values,
            null,
            $description
        );
    }
    
    /**
     * Log data access/viewing
     */
    public function logView($table_name, $record_id = null, $description = null) {
        $description = $description ?? "Accessed {$table_name} data";
        
        return $this->log(
            self::ACTION_VIEW,
            $table_name,
            $record_id,
            null,
            ['action' => 'view', 'timestamp' => date('Y-m-d H:i:s')],
            $description
        );
    }
    
    /**
     * Log access denied attempts
     */
    public function logAccessDenied($attempted_resource, $description = null) {
        $description = $description ?? "Access denied to {$attempted_resource}";
        
        return $this->log(
            self::ACTION_ACCESS_DENIED,
            'security',
            null,
            null,
            ['resource' => $attempted_resource, 'user_role' => $_SESSION['user_role'] ?? 'unknown'],
            $description
        );
    }
    
    /**
     * Log data export
     */
    public function logExport($table_name, $filters = null, $record_count = null) {
        $description = "Data exported from {$table_name}";
        if ($record_count) {
            $description .= " ({$record_count} records)";
        }
        
        return $this->log(
            self::ACTION_EXPORT,
            $table_name,
            null,
            null,
            ['filters' => $filters, 'record_count' => $record_count],
            $description
        );
    }
    
    /**
     * Log system settings change
     */
    public function logSettingsChange($setting_name, $old_value, $new_value) {
        return $this->log(
            self::ACTION_SETTINGS_CHANGE,
            'system_settings',
            null,
            ['setting' => $setting_name, 'value' => $old_value],
            ['setting' => $setting_name, 'value' => $new_value],
            "System setting '{$setting_name}' changed"
        );
    }
    
    /**
     * Get audit logs with filtering
     */
    public function getLogs($filters = [], $limit = 100, $offset = 0) {
        $where_conditions = [];
        $params = [];
        
        // Build WHERE clause
        if (!empty($filters['user_id'])) {
            $where_conditions[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $where_conditions[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $where_conditions[] = "al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(al.description LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Add pagination parameters
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare("
            SELECT 
                al.*,
                u.full_name as user_name,
                u.email as user_email,
                u.role as user_role
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            {$where_clause}
            ORDER BY al.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get audit log count for pagination
     */
    public function getLogCount($filters = []) {
        $where_conditions = [];
        $params = [];
        
        // Same filtering logic as getLogs()
        if (!empty($filters['user_id'])) {
            $where_conditions[] = "al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['action'])) {
            $where_conditions[] = "al.action = ?";
            $params[] = $filters['action'];
        }
        
        if (!empty($filters['table_name'])) {
            $where_conditions[] = "al.table_name = ?";
            $params[] = $filters['table_name'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_conditions[] = "DATE(al.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_conditions[] = "DATE(al.created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(al.description LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            {$where_clause}
        ");
        
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }
    
    /**
     * Get audit statistics
     */
    public function getStats($days = 30) {
        $date_from = date('Y-m-d', strtotime("-{$days} days"));
        
        // Total logs in period
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM audit_logs
            WHERE DATE(created_at) >= ?
        ");
        $stmt->execute([$date_from]);
        $total_logs = $stmt->fetch()['total'];
        
        // Logs by action type
        $stmt = $this->pdo->prepare("
            SELECT action, COUNT(*) as count
            FROM audit_logs
            WHERE DATE(created_at) >= ?
            GROUP BY action
            ORDER BY count DESC
        ");
        $stmt->execute([$date_from]);
        $by_action = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Most active users
        $stmt = $this->pdo->prepare("
            SELECT 
                u.full_name,
                u.email,
                u.role,
                COUNT(al.log_id) as action_count
            FROM audit_logs al
            JOIN users u ON al.user_id = u.user_id
            WHERE DATE(al.created_at) >= ?
            GROUP BY u.user_id
            ORDER BY action_count DESC
            LIMIT 10
        ");
        $stmt->execute([$date_from]);
        $active_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Daily activity
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM audit_logs
            WHERE DATE(created_at) >= ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        $stmt->execute([$date_from]);
        $daily_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total_logs' => $total_logs,
            'by_action' => $by_action,
            'active_users' => $active_users,
            'daily_activity' => $daily_activity
        ];
    }
    
    /**
     * Compare old and new values to find changed fields
     */
    private function getChangedFields($old_values, $new_values) {
        if (!is_array($old_values) || !is_array($new_values)) {
            return [];
        }
        
        $changes = [];
        foreach ($new_values as $field => $new_value) {
            $old_value = $old_values[$field] ?? null;
            if ($old_value !== $new_value) {
                $changes[$field] = [
                    'old' => $old_value,
                    'new' => $new_value
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Enable/disable logging
     */
    public function setEnabled($enabled) {
        $this->enabled = (bool) $enabled;
    }
    
    /**
     * Set current user ID manually
     */
    public function setUserId($user_id) {
        $this->current_user_id = $user_id;
    }
    
    /**
     * Clean old audit logs (for maintenance)
     */
    public function cleanOldLogs($days_to_keep = 365) {
        $cutoff_date = date('Y-m-d', strtotime("-{$days_to_keep} days"));
        
        $stmt = $this->pdo->prepare("
            DELETE FROM audit_logs 
            WHERE DATE(created_at) < ?
        ");
        
        $stmt->execute([$cutoff_date]);
        return $stmt->rowCount();
    }
}

// Global audit logger instance
if (!isset($GLOBALS['audit_logger'])) {
    $GLOBALS['audit_logger'] = new AuditLogger();
}

/**
 * Global helper function for quick audit logging
 */
function audit_log($action, $table_name, $record_id = null, $old_values = null, $new_values = null, $description = null) {
    global $audit_logger;
    if ($audit_logger) {
        return $audit_logger->log($action, $table_name, $record_id, $old_values, $new_values, $description);
    }
    return false;
}
?>