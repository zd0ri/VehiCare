<?php
/**
 * Service Model
 * VehiCare Service Management System
 */

require_once __DIR__ . '/BaseModel.php';

class Service extends BaseModel {
    protected $table = 'services';
    protected $primaryKey = 'service_id';
    protected $fillable = [
        'service_name', 'description', 'category', 'estimated_duration', 
        'base_price', 'labor_cost', 'is_active', 'requires_parts', 'skill_level'
    ];
    
    /**
     * Get active services
     */
    public function getActiveServices() {
        return $this->findAll('is_active = 1', [], 'service_name ASC');
    }
    
    /**
     * Get services by category
     */
    public function getServicesByCategory($category = null) {
        if ($category) {
            return $this->findAll('category = ? AND is_active = 1', [$category], 'service_name ASC');
        }
        
        return $this->getActiveServices();
    }
    
    /**
     * Get service categories
     */
    public function getCategories() {
        try {
            $sql = "SELECT DISTINCT category FROM {$this->table} WHERE is_active = 1 AND category IS NOT NULL ORDER BY category";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return array_column($stmt->fetchAll(), 'category');
        } catch (Exception $e) {
            log_event('ERROR', "Error getting service categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search services
     */
    public function searchServices($term) {
        return $this->findAll(
            '(service_name LIKE ? OR description LIKE ? OR category LIKE ?) AND is_active = 1',
            ["%{$term}%", "%{$term}%", "%{$term}%"],
            'service_name ASC'
        );
    }
    
    /**
     * Get popular services (most booked)
     */
    public function getPopularServices($limit = 10) {
        try {
            $sql = "
                SELECT 
                    s.*,
                    COUNT(a.appointment_id) as booking_count
                FROM {$this->table} s
                LEFT JOIN appointments a ON s.service_id = a.service_id
                WHERE s.is_active = 1
                GROUP BY s.service_id
                ORDER BY booking_count DESC, s.service_name ASC
                LIMIT ?
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            log_event('ERROR', "Error getting popular services: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get service statistics
     */
    public function getStats() {
        try {
            $stats = [
                'total_services' => $this->count(),
                'active_services' => $this->count('is_active = 1'),
                'inactive_services' => $this->count('is_active = 0'),
                'total_categories' => count($this->getCategories())
            ];
            
            // Average price
            $sql = "SELECT AVG(base_price) as avg_price FROM {$this->table} WHERE is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['average_price'] = round($result['avg_price'] ?? 0, 2);
            
            // Price range
            $sql = "SELECT MIN(base_price) as min_price, MAX(base_price) as max_price FROM {$this->table} WHERE is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['min_price'] = $result['min_price'] ?? 0;
            $stats['max_price'] = $result['max_price'] ?? 0;
            
            return $stats;
        } catch (Exception $e) {
            log_event('ERROR', "Error getting service stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get services with booking count
     */
    public function getServicesWithBookingCount() {
        try {
            $sql = "
                SELECT 
                    s.*,
                    COUNT(a.appointment_id) as booking_count,
                    COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_count
                FROM {$this->table} s
                LEFT JOIN appointments a ON s.service_id = a.service_id
                GROUP BY s.service_id
                ORDER BY s.service_name ASC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            log_event('ERROR', "Error getting services with booking count: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Activate service
     */
    public function activateService($serviceId) {
        return $this->update($serviceId, ['is_active' => 1]);
    }
    
    /**
     * Deactivate service
     */
    public function deactivateService($serviceId) {
        return $this->update($serviceId, ['is_active' => 0]);
    }
    
    /**
     * Update service pricing
     */
    public function updatePricing($serviceId, $basePrice, $laborCost = null) {
        $data = ['base_price' => $basePrice];
        if ($laborCost !== null) {
            $data['labor_cost'] = $laborCost;
        }
        
        return $this->update($serviceId, $data);
    }
    
    /**
     * Get services by skill level
     */
    public function getServicesBySkillLevel($skillLevel) {
        return $this->findAll('skill_level = ? AND is_active = 1', [$skillLevel], 'service_name ASC');
    }
    
    /**
     * Get services requiring parts
     */
    public function getServicesRequiringParts() {
        return $this->findAll('requires_parts = 1 AND is_active = 1', [], 'service_name ASC');
    }
}
?>