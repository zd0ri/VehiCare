<?php
/**
 * User Model
 * VehiCare Service Management System
 */

require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'email', 'password', 'full_name', 'phone', 'address', 'role', 'status', 'profile_picture', 'email_verified'
    ];
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (Exception $e) {
            log_event('ERROR', "Error finding user by email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        return $this->findAll('role = ? AND status = ?', [$role, 'active'], 'full_name ASC');
    }
    
    /**
     * Get all clients
     */
    public function getClients() {
        return $this->getByRole('client');
    }
    
    /**
     * Get all technicians (staff)
     */
    public function getTechnicians() {
        return $this->getByRole('staff');
    }
    
    /**
     * Get all admins
     */
    public function getAdmins() {
        return $this->getByRole('admin');
    }
    
    /**
     * Create new user with hashed password
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Search users
     */
    public function searchUsers($term, $role = null) {
        $where = '(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)';
        $params = ["%{$term}%", "%{$term}%", "%{$term}%"];
        
        if ($role) {
            $where .= ' AND role = ?';
            $params[] = $role;
        }
        
        return $this->findAll($where, $params, 'full_name ASC');
    }
    
    /**
     * Get user statistics
     */
    public function getStats() {
        try {
            $stats = [
                'total_users' => $this->count(),
                'active_users' => $this->count('status = ?', ['active']),
                'total_clients' => $this->count('role = ?', ['client']),
                'active_clients' => $this->count('role = ? AND status = ?', ['client', 'active']),
                'total_staff' => $this->count('role = ?', ['staff']),
                'active_staff' => $this->count('role = ? AND status = ?', ['staff', 'active']),
                'total_admins' => $this->count('role = ?', ['admin'])
            ];
            
            return $stats;
        } catch (Exception $e) {
            log_event('ERROR', "Error getting user stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent registrations
     */
    public function getRecentRegistrations($limit = 10) {
        return $this->findAll('', [], 'created_date DESC', $limit);
    }
    
    /**
     * Deactivate user
     */
    public function deactivateUser($userId) {
        return $this->update($userId, ['status' => 'inactive']);
    }
    
    /**
     * Activate user
     */
    public function activateUser($userId) {
        return $this->update($userId, ['status' => 'active']);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        $where = 'email = ?';
        $params = [$email];
        
        if ($excludeUserId) {
            $where .= ' AND user_id != ?';
            $params[] = $excludeUserId;
        }
        
        return $this->count($where, $params) > 0;
    }
}
?>