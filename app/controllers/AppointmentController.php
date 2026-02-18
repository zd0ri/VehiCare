<?php
/**
 * Appointment Controller
 * VehiCare Service Management System
 * 
 * Handles all appointment-related operations (CRUD)
 */

require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../middleware/RBAC.php';

class AppointmentController {
    private $appointmentModel;
    private $userModel;
    private $vehicleModel;
    private $serviceModel;
    private $rbac;
    
    public function __construct($pdo) {
        $this->appointmentModel = new Appointment($pdo);
        $this->userModel = new User($pdo);
        $this->vehicleModel = new Vehicle($pdo);
        $this->serviceModel = new Service($pdo);
        $this->rbac = new RBAC($pdo);
    }
    
    /**
     * Display appointments list
     */
    public function index($userId, $userRole) {
        try {
            // Check permissions
            if (!$this->rbac->canAccess($userRole, 'appointments', 'view')) {
                return $this->error('Access denied', 403);
            }
            
            // Get appointments based on role
            switch ($userRole) {
                case 'admin':
                    $appointments = $this->appointmentModel->getAppointmentsWithDetails();
                    break;
                case 'staff':
                    $appointments = $this->appointmentModel->getAppointmentsByTechnician($userId);
                    break;
                case 'client':
                    $appointments = $this->appointmentModel->getAppointmentsByClient($userId);
                    break;
                default:
                    return $this->error('Invalid role', 400);
            }
            
            return $this->success('Appointments retrieved successfully', $appointments);
            
        } catch (Exception $e) {
            log_event('ERROR', "Error getting appointments: " . $e->getMessage());
            return $this->error('Failed to retrieve appointments');
        }
    }
    
    /**
     * Show appointment details
     */
    public function show($appointmentId, $userId, $userRole) {
        try {
            // Get appointment with details
            $appointments = $this->appointmentModel->getAppointmentsWithDetails('a.appointment_id = ?', [$appointmentId]);
            
            if (empty($appointments)) {
                return $this->error('Appointment not found', 404);
            }
            
            $appointment = $appointments[0];
            
            // Check permissions
            if (!$this->canAccessAppointment($appointment, $userId, $userRole)) {
                return $this->error('Access denied', 403);
            }
            
            return $this->success('Appointment retrieved successfully', $appointment);
            
        } catch (Exception $e) {
            log_event('ERROR', "Error getting appointment: " . $e->getMessage());
            return $this->error('Failed to retrieve appointment');
        }
    }
    
    /**
     * Create new appointment
     */
    public function create($data, $userId, $userRole) {
        try {
            // Check permissions
            if (!$this->rbac->canAccess($userRole, 'appointments', 'create')) {
                return $this->error('Access denied', 403);
            }
            
            // Validate required fields
            $requiredFields = ['client_id', 'vehicle_id', 'service_id', 'appointment_date', 'appointment_time'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->error("Field '$field' is required", 400);
                }
            }
            
            // For clients, ensure they can only book for themselves
            if ($userRole === 'client' && $data['client_id'] != $userId) {
                return $this->error('You can only book appointments for yourself', 403);
            }
            
            // Validate client exists
            $client = $this->userModel->find($data['client_id']);
            if (!$client || $client['role'] !== 'client') {
                return $this->error('Invalid client', 400);
            }
            
            // Validate vehicle belongs to client
            $vehicle = $this->vehicleModel->find($data['vehicle_id']);
            if (!$vehicle || $vehicle['client_id'] != $data['client_id']) {
                return $this->error('Vehicle does not belong to the specified client', 400);
            }
            
            // Validate service exists
            $service = $this->serviceModel->find($data['service_id']);
            if (!$service || !$service['is_active']) {
                return $this->error('Invalid or inactive service', 400);
            }
            
            // Check for scheduling conflicts
            $technicianId = $data['technician_id'] ?? null;
            if ($this->appointmentModel->hasSchedulingConflict(
                $data['appointment_date'], 
                $data['appointment_time'], 
                $technicianId
            )) {
                return $this->error('Time slot is already booked', 409);
            }
            
            // Set default values
            $data['status'] = $data['status'] ?? 'pending';
            $data['priority'] = $data['priority'] ?? 'normal';
            $data['booking_type'] = $data['booking_type'] ?? 'online';
            $data['total_estimated_cost'] = $service['base_price'];
            
            // Calculate estimated completion
            if (!empty($service['estimated_duration'])) {
                $appointmentDateTime = $data['appointment_date'] . ' ' . $data['appointment_time'];
                $estimatedCompletion = date('Y-m-d H:i:s', 
                    strtotime($appointmentDateTime . ' + ' . $service['estimated_duration'] . ' minutes')
                );
                $data['estimated_completion'] = $estimatedCompletion;
            }
            
            // Create appointment
            $appointment = $this->appointmentModel->create($data);
            
            if ($appointment) {
                // Log the action
                $this->logAudit($userId, 'CREATE', 'appointments', $appointment['appointment_id'], null, $appointment, 'Created new appointment');
                
                // Send notification to client
                $this->sendNotification($data['client_id'], 'Appointment Scheduled', 'Your appointment has been scheduled successfully.');
                
                return $this->success('Appointment created successfully', $appointment);
            }
            
            return $this->error('Failed to create appointment');
            
        } catch (Exception $e) {
            log_event('ERROR', "Error creating appointment: " . $e->getMessage());
            return $this->error('Failed to create appointment');
        }
    }
    
    /**
     * Update appointment
     */
    public function update($appointmentId, $data, $userId, $userRole) {
        try {
            // Get existing appointment
            $appointments = $this->appointmentModel->getAppointmentsWithDetails('a.appointment_id = ?', [$appointmentId]);
            
            if (empty($appointments)) {
                return $this->error('Appointment not found', 404);
            }
            
            $existingAppointment = $appointments[0];
            
            // Check permissions
            if (!$this->canModifyAppointment($existingAppointment, $userId, $userRole)) {
                return $this->error('Access denied', 403);
            }
            
            // Validate fields if provided
            if (isset($data['appointment_date']) && isset($data['appointment_time'])) {
                // Check for scheduling conflicts (excluding current appointment)
                $technicianId = $data['technician_id'] ?? $existingAppointment['technician_id'];
                if ($this->appointmentModel->hasSchedulingConflict(
                    $data['appointment_date'], 
                    $data['appointment_time'], 
                    $technicianId,
                    $appointmentId
                )) {
                    return $this->error('Time slot is already booked', 409);
                }
            }
            
            // Update estimated cost and completion if service changed
            if (isset($data['service_id']) && $data['service_id'] != $existingAppointment['service_id']) {
                $service = $this->serviceModel->find($data['service_id']);
                if ($service) {
                    $data['total_estimated_cost'] = $service['base_price'];
                    
                    // Recalculate estimated completion
                    if (!empty($service['estimated_duration'])) {
                        $appointmentDate = $data['appointment_date'] ?? $existingAppointment['appointment_date'];
                        $appointmentTime = $data['appointment_time'] ?? $existingAppointment['appointment_time'];
                        $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
                        
                        $estimatedCompletion = date('Y-m-d H:i:s', 
                            strtotime($appointmentDateTime . ' + ' . $service['estimated_duration'] . ' minutes')
                        );
                        $data['estimated_completion'] = $estimatedCompletion;
                    }
                }
            }
            
            // Update appointment
            $updatedAppointment = $this->appointmentModel->update($appointmentId, $data);
            
            if ($updatedAppointment) {
                // Log the action
                $this->logAudit($userId, 'UPDATE', 'appointments', $appointmentId, $existingAppointment, $updatedAppointment, 'Updated appointment');
                
                return $this->success('Appointment updated successfully', $updatedAppointment);
            }
            
            return $this->error('Failed to update appointment');
            
        } catch (Exception $e) {
            log_event('ERROR', "Error updating appointment: " . $e->getMessage());
            return $this->error('Failed to update appointment');
        }
    }
    
    /**
     * Update appointment status
     */
    public function updateStatus($appointmentId, $status, $notes, $userId, $userRole) {
        try {
            // Get existing appointment
            $appointments = $this->appointmentModel->getAppointmentsWithDetails('a.appointment_id = ?', [$appointmentId]);
            
            if (empty($appointments)) {
                return $this->error('Appointment not found', 404);
            }
            
            $existingAppointment = $appointments[0];
            
            // Check permissions
            if (!$this->canModifyAppointment($existingAppointment, $userId, $userRole)) {
                return $this->error('Access denied', 403);
            }
            
            // Validate status
            $allowedStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
            if (!in_array($status, $allowedStatuses)) {
                return $this->error('Invalid status', 400);
            }
            
            // Update status
            $result = $this->appointmentModel->updateStatus($appointmentId, $status, $notes);
            
            if ($result) {
                // Log the action
                $this->logAudit($userId, 'STATUS_UPDATE', 'appointments', $appointmentId, 
                    ['status' => $existingAppointment['status']], 
                    ['status' => $status], 
                    'Updated appointment status to: ' . $status);
                
                // Send notification based on status
                $this->sendStatusNotification($existingAppointment['client_id'], $status, $appointmentId);
                
                return $this->success('Appointment status updated successfully');
            }
            
            return $this->error('Failed to update appointment status');
            
        } catch (Exception $e) {
            log_event('ERROR', "Error updating appointment status: " . $e->getMessage());
            return $this->error('Failed to update appointment status');
        }
    }
    
    /**
     * Cancel appointment
     */
    public function cancel($appointmentId, $reason, $userId, $userRole) {
        return $this->updateStatus($appointmentId, 'cancelled', $reason, $userId, $userRole);
    }
    
    /**
     * Delete appointment
     */
    public function delete($appointmentId, $userId, $userRole) {
        try {
            // Check permissions (usually only admins can delete)
            if ($userRole !== 'admin') {
                return $this->error('Access denied', 403);
            }
            
            // Get existing appointment for audit trail
            $appointment = $this->appointmentModel->find($appointmentId);
            
            if (!$appointment) {
                return $this->error('Appointment not found', 404);
            }
            
            // Delete appointment
            if ($this->appointmentModel->delete($appointmentId)) {
                // Log the action
                $this->logAudit($userId, 'DELETE', 'appointments', $appointmentId, $appointment, null, 'Deleted appointment');
                
                return $this->success('Appointment deleted successfully');
            }
            
            return $this->error('Failed to delete appointment');
            
        } catch (Exception $e) {
            log_event('ERROR', "Error deleting appointment: " . $e->getMessage());
            return $this->error('Failed to delete appointment');
        }
    }
    
    /**
     * Get available time slots
     */
    public function getAvailableSlots($date, $technicianId = null) {
        try {
            $slots = $this->appointmentModel->getAvailableTimeSlots($date, $technicianId);
            return $this->success('Available time slots retrieved', $slots);
        } catch (Exception $e) {
            log_event('ERROR', "Error getting available slots: " . $e->getMessage());
            return $this->error('Failed to get available slots');
        }
    }
    
    /**
     * Search appointments
     */
    public function search($term, $userId, $userRole) {
        try {
            $appointments = $this->appointmentModel->searchAppointments($term, $userId, $userRole);
            return $this->success('Search results retrieved', $appointments);
        } catch (Exception $e) {
            log_event('ERROR', "Error searching appointments: " . $e->getMessage());
            return $this->error('Failed to search appointments');
        }
    }
    
    /**
     * Private helper methods
     */
    
    private function canAccessAppointment($appointment, $userId, $userRole) {
        switch ($userRole) {
            case 'admin':
                return true;
            case 'staff':
                return $appointment['technician_id'] == $userId;
            case 'client':
                return $appointment['client_id'] == $userId;
            default:
                return false;
        }
    }
    
    private function canModifyAppointment($appointment, $userId, $userRole) {
        // Same logic as access for now, but could be different in the future
        return $this->canAccessAppointment($appointment, $userId, $userRole);
    }
    
    private function sendNotification($recipientId, $title, $message) {
        // This would integrate with your notification system
        // For now, just log it
        log_event('INFO', "Notification sent to user $recipientId: $title - $message");
    }
    
    private function sendStatusNotification($clientId, $status, $appointmentId) {
        $messages = [
            'confirmed' => 'Your appointment has been confirmed.',
            'in_progress' => 'Your vehicle service has started.',
            'completed' => 'Your vehicle service has been completed.',
            'cancelled' => 'Your appointment has been cancelled.',
        ];
        
        if (isset($messages[$status])) {
            $this->sendNotification($clientId, 'Appointment Update', $messages[$status]);
        }
    }
    
    private function logAudit($userId, $action, $table, $recordId, $oldValues, $newValues, $description) {
        // This would integrate with your audit logging system
        log_event('INFO', "AUDIT: User $userId performed $action on $table record $recordId - $description");
    }
    
    private function success($message, $data = null) {
        $response = ['success' => true, 'message' => $message];
        if ($data !== null) {
            $response['data'] = $data;
        }
        return $response;
    }
    
    private function error($message, $code = 500) {
        return ['success' => false, 'message' => $message, 'code' => $code];
    }
}
?>