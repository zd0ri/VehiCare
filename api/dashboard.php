<?php
/**
 * Dashboard API - Real-time Statistics
 * Provides JSON data for dashboard widgets and charts
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/auth_helpers.php';

// Check authentication
if (!checkAuth(false)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$action = $_GET['action'] ?? 'stats';

try {
    switch ($action) {
        case 'stats':
            echo json_encode(getDashboardStats());
            break;
            
        case 'charts':
            echo json_encode(getChartData());
            break;
            
        case 'recent_activity':
            echo json_encode(getRecentActivity());
            break;
            
        case 'notifications':
            echo json_encode(getNotifications());
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Dashboard API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Get dashboard statistics
 */
function getDashboardStats() {
    global $pdo;
    $user = getCurrentUser();
    $today = date('Y-m-d');
    $this_month = date('Y-m-01');
    $last_month_start = date('Y-m-01', strtotime('-1 month'));
    $last_month_end = date('Y-m-t', strtotime('-1 month'));
    
    $stats = [];
    
    if (hasPermission('view_all_appointments')) {
        // Admin stats
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM appointments");
        $stats['total_appointments'] = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as today FROM appointments WHERE DATE(appointment_date) = ?");
        $stmt->execute([$today]);
        $stats['today_appointments'] = $stmt->fetch()['today'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM appointments WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_appointments'] = $stmt->fetch()['pending'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as confirmed FROM appointments WHERE status = 'confirmed'");
        $stmt->execute();
        $stats['confirmed_appointments'] = $stmt->fetch()['confirmed'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM appointments WHERE status = 'completed' AND DATE(appointment_date) = ?");
        $stmt->execute([$today]);
        $stats['completed_today'] = $stmt->fetch()['completed'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as clients FROM users WHERE role = 'client'");
        $stats['total_clients'] = $stmt->fetch()['clients'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as vehicles FROM vehicles");
        $stats['total_vehicles'] = $stmt->fetch()['vehicles'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as services FROM services WHERE is_active = 1");
        $stats['active_services'] = $stmt->fetch()['services'];
        
    } else {
        // Staff stats
        $user_id = $user['user_id'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as assignments FROM appointments WHERE assigned_technician = ?");
        $stmt->execute([$user_id]);
        $stats['my_assignments'] = $stmt->fetch()['assignments'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as pending FROM appointments WHERE assigned_technician = ? AND status = 'pending'");
        $stmt->execute([$user_id]);
        $stats['my_pending'] = $stmt->fetch()['pending'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as in_progress FROM appointments WHERE assigned_technician = ? AND status = 'in-progress'");
        $stmt->execute([$user_id]);
        $stats['my_in_progress'] = $stmt->fetch()['in_progress'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM appointments WHERE assigned_technician = ? AND status = 'completed' AND DATE(appointment_date) = ?");
        $stmt->execute([$user_id, $today]);
        $stats['my_completed_today'] = $stmt->fetch()['completed'];
    }
    
    // Calculate percentage changes
    $stmt = $pdo->prepare("SELECT COUNT(*) as last_month FROM appointments WHERE appointment_date >= ? AND appointment_date <= ?");
    $stmt->execute([$last_month_start, $last_month_end]);
    $last_month_count = $stmt->fetch()['last_month'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as this_month FROM appointments WHERE appointment_date >= ?");
    $stmt->execute([$this_month]);
    $this_month_count = $stmt->fetch()['this_month'];
    
    $stats['appointments_change'] = $last_month_count > 0 
        ? round((($this_month_count - $last_month_count) / $last_month_count) * 100, 1)
        : 0;
    
    return $stats;
}

/**
 * Get chart data
 */
function getChartData() {
    global $pdo;
    
    $charts = [];
    
    if (hasPermission('view_all_appointments')) {
        // Appointments by status
        $stmt = $pdo->query("
            SELECT status, COUNT(*) as count 
            FROM appointments 
            GROUP BY status
        ");
        $status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $charts['appointments_by_status'] = [
            'labels' => array_column($status_data, 'status'),
            'data' => array_column($status_data, 'count'),
            'colors' => ['#ffc107', '#17a2b8', '#dc143c', '#28a745', '#6c757d']
        ];
        
        // Monthly appointments trend (last 6 months)
        $monthly_data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i month"));
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM appointments 
                WHERE DATE_FORMAT(appointment_date, '%Y-%m') = ?
            ");
            $stmt->execute([$month]);
            $count = $stmt->fetch()['count'];
            
            $monthly_data[] = [
                'month' => date('M Y', strtotime("-$i month")),
                'count' => $count
            ];
        }
        
        $charts['monthly_trend'] = [
            'labels' => array_column($monthly_data, 'month'),
            'data' => array_column($monthly_data, 'count')
        ];
        
        // Services popularity
        $stmt = $pdo->query("
            SELECT s.name, COUNT(a.appointment_id) as count
            FROM services s
            LEFT JOIN appointments a ON s.service_id = a.service_id
            WHERE s.is_active = 1
            GROUP BY s.service_id, s.name
            ORDER BY count DESC
            LIMIT 5
        ");
        $service_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $charts['popular_services'] = [
            'labels' => array_column($service_data, 'name'),
            'data' => array_column($service_data, 'count')
        ];
    }
    
    return $charts;
}

/**
 * Get recent activity
 */
function getRecentActivity() {
    global $pdo;
    $user = getCurrentUser();
    
    $conditions = [];
    $params = [];
    
    if (!hasPermission('view_all_appointments')) {
        $conditions[] = "a.assigned_technician = ?";
        $params[] = $user['user_id'];
    }
    
    $where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_date,
            a.appointment_time,
            a.status,
            u.full_name as client_name,
            s.name as service_name,
            v.car_brand,
            v.car_model,
            v.plate_number
        FROM appointments a
        JOIN users u ON a.client_id = u.user_id
        LEFT JOIN services s ON a.service_id = s.service_id  
        LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        $where_clause
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 10
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get notifications
 */
function getNotifications() {
    global $pdo;
    $user = getCurrentUser();
    
    $stmt = $pdo->prepare("
        SELECT 
            notification_id,
            title,
            message,
            type,
            is_read,
            created_at
        FROM notifications 
        WHERE user_id = ? OR user_id IS NULL
        ORDER BY created_at DESC
        LIMIT 20
    ");
    
    $stmt->execute([$user['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count unread notifications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $stmt->execute([$user['user_id']]);
    $unread_count = $stmt->fetch()['unread_count'];
    
    return [
        'items' => $notifications,
        'unread_count' => $unread_count
    ];
}
?>