<?php
/**
 * Appointment Model
 * VehiCare Service Management System
 */

require_once __DIR__ . '/BaseModel.php';

class Appointment extends BaseModel {
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    protected $fillable = [
        'client_id', 'vehicle_id', 'service_id', 'technician_id', 'appointment_date', 'appointment_time', 
        'estimated_completion', 'actual_completion', 'status', 'priority', 'customer_notes', 
        'technician_notes', 'total_estimated_cost', 'actual_cost', 'payment_status', 'booking_type'
    ];
    
    /**
     * Get appointments with related data
     */
    public function getAppointmentsWithDetails($where = '', $params = [], $orderBy = 'appointment_date DESC, appointment_time ASC') {
        try {
            $sql = "
                SELECT 
                    a.*,
                    c.full_name as client_name,
                    c.phone as client_phone,
                    c.email as client_email,
                    v.plate_number,
                    v.car_brand,
                    v.car_model,
                    v.year_model,
                    s.service_name,
                    s.estimated_duration,
                    s.base_price,
                    t.full_name as technician_name
                FROM {$this->table} a
                LEFT JOIN users c ON a.client_id = c.user_id
                LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
                LEFT JOIN services s ON a.service_id = s.service_id
                LEFT JOIN users t ON a.technician_id = t.user_id
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
            log_event('ERROR', "Error getting appointments with details: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get today's appointments
     */
    public function getTodaysAppointments($userId = null, $userRole = null) {
        $where = 'DATE(a.appointment_date) = CURDATE()';
        $params = [];
        
        // Apply role-based filtering
        if ($userRole === 'client' && $userId) {
            $where .= ' AND a.client_id = ?';
            $params[] = $userId;
        } elseif ($userRole === 'staff' && $userId) {
            $where .= ' AND a.technician_id = ?';
            $params[] = $userId;
        }
        
        return $this->getAppointmentsWithDetails($where, $params);
    }
    
    /**
     * Get upcoming appointments
     */
    public function getUpcomingAppointments($userId = null, $userRole = null, $days = 7) {
        $where = 'a.appointment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)';
        $params = [$days];
        
        // Apply role-based filtering
        if ($userRole === 'client' && $userId) {
            $where .= ' AND a.client_id = ?';
            $params[] = $userId;
        } elseif ($userRole === 'staff' && $userId) {
            $where .= ' AND a.technician_id = ?';
            $params[] = $userId;
        }
        
        return $this->getAppointmentsWithDetails($where, $params);
    }
    
    /**
     * Get appointments by status
     */
    public function getAppointmentsByStatus($status, $userId = null, $userRole = null) {
        $where = 'a.status = ?';
        $params = [$status];
        
        // Apply role-based filtering
        if ($userRole === 'client' && $userId) {
            $where .= ' AND a.client_id = ?';
            $params[] = $userId;
        } elseif ($userRole === 'staff' && $userId) {
            $where .= ' AND a.technician_id = ?';
            $params[] = $userId;
        }
        
        return $this->getAppointmentsWithDetails($where, $params);
    }
    
    /**
     * Get appointments by client
     */
    public function getAppointmentsByClient($clientId) {
        return $this->getAppointmentsWithDetails('a.client_id = ?', [$clientId]);
    }
    
    /**
     * Get appointments by technician
     */
    public function getAppointmentsByTechnician($technicianId) {
        return $this->getAppointmentsWithDetails('a.technician_id = ?', [$technicianId]);
    }
    
    /**
     * Get appointments by vehicle
     */
    public function getAppointmentsByVehicle($vehicleId) {
        return $this->getAppointmentsWithDetails('a.vehicle_id = ?', [$vehicleId]);
    }
    
    /**
     * Update appointment status
     */
    public function updateStatus($appointmentId, $status, $notes = null) {
        $updateData = ['status' => $status];
        
        // Add completion time if status is completed
        if ($status === 'completed') {
            $updateData['actual_completion'] = date('Y-m-d H:i:s');
        }
        
        // Add technician notes if provided
        if ($notes !== null) {
            $updateData['technician_notes'] = $notes;
        }
        
        return $this->update($appointmentId, $updateData);
    }
    
    /**
     * Assign technician to appointment
     */
    public function assignTechnician($appointmentId, $technicianId) {
        return $this->update($appointmentId, ['technician_id' => $technicianId]);
    }
    
    /**
     * Check for scheduling conflicts
     */
    public function hasSchedulingConflict($appointmentDate, $appointmentTime, $technicianId = null, $excludeAppointmentId = null) {
        $where = 'appointment_date = ? AND appointment_time = ? AND status NOT IN (?, ?)';
        $params = [$appointmentDate, $appointmentTime, 'cancelled', 'completed'];
        
        if ($technicianId) {
            $where .= ' AND technician_id = ?';
            $params[] = $technicianId;
        }
        
        if ($excludeAppointmentId) {
            $where .= ' AND appointment_id != ?';
            $params[] = $excludeAppointmentId;
        }
        
        return $this->count($where, $params) > 0;
    }
    
    /**
     * Get available time slots for a date
     */
    public function getAvailableTimeSlots($date, $technicianId = null) {
        // Define business hours (8 AM to 6 PM)
        $businessHours = [
            '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
            '11:00', '11:30', '13:00', '13:30', '14:00', '14:30',
            '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'
        ];
        
        // Get booked slots
        $where = 'appointment_date = ? AND status NOT IN (?, ?)';
        $params = [$date, 'cancelled', 'completed'];
        
        if ($technicianId) {
            $where .= ' AND technician_id = ?';
            $params[] = $technicianId;
        }
        
        $bookedAppointments = $this->findAll($where, $params);
        $bookedSlots = array_column($bookedAppointments, 'appointment_time');
        
        // Return available slots
        $availableSlots = [];
        foreach ($businessHours as $slot) {
            if (!in_array($slot . ':00', $bookedSlots)) {
                $availableSlots[] = $slot;
            }
        }
        
        return $availableSlots;
    }
    
    /**
     * Get appointment statistics
     */
    public function getStats($userId = null, $userRole = null) {
        try {
            // Base conditions
            $whereConditions = [];
            $params = [];
            
            // Apply role-based filtering
            if ($userRole === 'client' && $userId) {
                $whereConditions[] = 'client_id = ?';
                $params[] = $userId;
            } elseif ($userRole === 'staff' && $userId) {
                $whereConditions[] = 'technician_id = ?';
                $params[] = $userId;
            }
            
            $whereClause = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '';
            
            $stats = [
                'total_appointments' => $this->count($whereClause, $params),
                'today_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'DATE(appointment_date) = CURDATE()',
                    $params
                ),
                'pending_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['pending'])
                ),
                'confirmed_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['confirmed'])
                ),
                'in_progress_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['in_progress'])
                ),
                'completed_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['completed'])
                ),
                'cancelled_appointments' => $this->count(
                    ($whereClause ? $whereClause . ' AND ' : '') . 'status = ?',
                    array_merge($params, ['cancelled'])
                )
            ];
            
            // Monthly stats
            $stats['this_month_appointments'] = $this->count(
                ($whereClause ? $whereClause . ' AND ' : '') . 'YEAR(appointment_date) = YEAR(CURDATE()) AND MONTH(appointment_date) = MONTH(CURDATE())',
                $params
            );
            
            return $stats;
        } catch (Exception $e) {
            log_event('ERROR', "Error getting appointment stats: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search appointments
     */
    public function searchAppointments($term, $userId = null, $userRole = null) {
        $where = '(c.full_name LIKE ? OR v.plate_number LIKE ? OR s.service_name LIKE ?)';
        $params = ["%{$term}%", "%{$term}%", "%{$term}%"];
        
        // Apply role-based filtering
        if ($userRole === 'client' && $userId) {
            $where .= ' AND a.client_id = ?';
            $params[] = $userId;
        } elseif ($userRole === 'staff' && $userId) {
            $where .= ' AND a.technician_id = ?';
            $params[] = $userId;
        }
        
        return $this->getAppointmentsWithDetails($where, $params);
    }
}
?>