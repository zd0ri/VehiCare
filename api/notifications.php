<?php
/**
 * Notifications API
 * Handles notification management
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/auth_helpers.php';

if (!checkAuth(false)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user = getCurrentUser();

try {
    switch ($method) {
        case 'GET':
            echo json_encode(getNotifications($user['user_id']));
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            
            if ($action === 'mark_read') {
                echo json_encode(markNotificationRead($input['id'], $user['user_id']));
            } elseif ($action === 'mark_all_read') {
                echo json_encode(markAllNotificationsRead($user['user_id']));
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    error_log("Notifications API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

function getNotifications($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT 
            notification_id,
            title,
            message,
            type,
            is_read,
            created_at,
            TIMESTAMPDIFF(MINUTE, created_at, NOW()) as minutes_ago
        FROM notifications 
        WHERE user_id = ? OR user_id IS NULL
        ORDER BY created_at DESC
        LIMIT 20
    ");
    
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format time
    foreach ($notifications as &$notification) {
        $notification['time_ago'] = formatTimeAgo($notification['minutes_ago']);
    }
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications 
        WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetch()['unread_count'];
    
    return [
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ];
}

function markNotificationRead($notification_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notification_id = ? AND (user_id = ? OR user_id IS NULL)
    ");
    
    return ['success' => $stmt->execute([$notification_id, $user_id])];
}

function markAllNotificationsRead($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0
    ");
    
    return ['success' => $stmt->execute([$user_id])];
}

function formatTimeAgo($minutes) {
    if ($minutes < 1) return 'Just now';
    if ($minutes < 60) return $minutes . ' min ago';
    
    $hours = floor($minutes / 60);
    if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    
    $days = floor($hours / 24);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}
?>