<?php
/**
 * Vehicle Model
 * VehiCare Service Management System
 */

require_once __DIR__ . '/BaseModel.php';

class Vehicle extends BaseModel {
    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    protected $fillable = [
        'client_id', 'plate_number', 'car_brand', 'car_model', 'year_model', 
        'color', 'engine_type', 'transmission_type', 'mileage', 'vin_number', 
        'insurance_info', 'notes', 'status'
    ];
    
    /**
     * Get vehicles with client information
     */
    public function getVehiclesWithClient($where = '', $params = [], $orderBy = 'v.car_brand ASC, v.car_model ASC') {
        try {
            $sql = "
                SELECT 
                    v.*,
                    u.full_name as client_name,
                    u.phone as client_phone,
                    u.email as client_email
                FROM {$this->table} v
                LEFT JOIN users u ON v.client_id = u.user_id
            ";
            
            if (!empty($where)) {
                $sql .= " WHERE " . $where;
            }
            
            if (!empty($orderBy)) {
                $sql .= " ORDER BY " . $orderBy;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            log_event('ERROR', "Error getting vehicles with client: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get vehicles by client
     */
    public function getVehiclesByClient($clientId) {
        return $this->findAll('client_id = ? AND status = ?', [$clientId, 'active'], 'car_brand ASC');
    }
    
    /**
     * Get vehicle with client details
     */
    public function getVehicleWithClient($vehicleId) {
        $vehicles = $this->getVehiclesWithClient('v.vehicle_id = ?', [$vehicleId]);
        return $vehicles ? $vehicles[0] : null;
    }
    
    /**
     * Check if plate number exists
     */
    public function plateNumberExists($plateNumber, $excludeVehicleId = null) {
        $where = 'plate_number = ?';
        $params = [$plateNumber];
        
        if ($excludeVehicleId) {
            $where .= ' AND vehicle_id != ?';
            $params[] = $excludeVehicleId;
        }
        
        return $this->count($where, $params) > 0;
    }
    
    /**
     * Search vehicles
     */
    public function searchVehicles($term, $clientId = null) {
        $where = '(v.plate_number LIKE ? OR v.car_brand LIKE ? OR v.car_model LIKE ? OR u.full_name LIKE ?)';
        $params = ["%{$term}%", "%{$term}%", "%{$term}%", "%{$term}%"];
        
        if ($clientId) {
            $where .= ' AND v.client_id = ?';
            $params[] = $clientId;
        }
        
        return $this->getVehiclesWithClient($where, $params);
    }
    
    /**
     * Get vehicle statistics
     */
    public function getStats($clientId = null) {
        try {
            $whereClause = $clientId ? 'client_id = ?' : '';
            $params = $clientId ? [$clientId] : [];
            
            $stats = [
                'total_vehicles' => $this->count($whereClause, $params),
                'active_vehicles' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['active'])
                )
            ];
            
            // Get brand distribution
            $sql = "SELECT car_brand, COUNT(*) as count FROM {$this->table}";
            if ($clientId) {
                $sql .= " WHERE client_id = ?";
            }
            $sql .= " GROUP BY car_brand ORDER BY count DESC LIMIT 5";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $stats['brand_distribution'] = $stmt->fetchAll();
            
            return $stats;
        } catch (Exception $e) {
            log_event('ERROR', "Error getting vehicle stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get recent vehicles
     */
    public function getRecentVehicles($limit = 10, $clientId = null) {
        $where = $clientId ? 'client_id = ?' : '';
        $params = $clientId ? [$clientId] : [];
        
        return $this->getVehiclesWithClient($where, $params, 'v.created_date DESC LIMIT ' . $limit);
    }
    
    /**
     * Get vehicle service history
     */
    public function getServiceHistory($vehicleId) {
        try {
            $sql = "
                SELECT 
                    a.*,
                    s.service_name,
                    u.full_name as technician_name
                FROM appointments a
                LEFT JOIN services s ON a.service_id = s.service_id
                LEFT JOIN users u ON a.technician_id = u.user_id
                WHERE a.vehicle_id = ?
                ORDER BY a.appointment_date DESC, a.appointment_time DESC
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$vehicleId]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            log_event('ERROR', "Error getting vehicle service history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update mileage
     */
    public function updateMileage($vehicleId, $mileage) {
        return $this->update($vehicleId, ['mileage' => $mileage]);
    }
    
    /**
     * Archive vehicle
     */
    public function archiveVehicle($vehicleId) {
        return $this->update($vehicleId, ['status' => 'archived']);
    }
    
    /**
     * Activate vehicle
     */
    public function activateVehicle($vehicleId) {
        return $this->update($vehicleId, ['status' => 'active']);
    }
}
?>