<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Queue & Notifications';
$page_icon = 'fas fa-bell';
include __DIR__ . '/includes/admin_layout_header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_notification') {
        $recipient_id = intval($_POST['recipient_id']);
        $title = $_POST['title'];
        $message = $_POST['message'];
        $notification_type = $_POST['notification_type'];
        
        $stmt = $conn->prepare("INSERT INTO notifications (recipient_id, sender_id, title, message, notification_type, created_at)
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('iisss', $recipient_id, $_SESSION['user_id'], $title, $message, $notification_type);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Notification sent successfully!";
        }
    }
    
    if ($_POST['action'] === 'update_queue_status') {
        $queue_id = intval($_POST['queue_id']);
        $new_status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE queue_management SET status = ?, updated_at = NOW() WHERE queue_id = ?");
        $stmt->bind_param('si', $new_status, $queue_id);
        
        if ($stmt->execute()) {
            
            $queue = $conn->query("SELECT appointment_id FROM queue_management WHERE queue_id = $queue_id")->fetch_assoc();
            if ($queue) {
                $appt = $conn->query("SELECT user_id FROM appointments WHERE appointment_id = {$queue['appointment_id']}")->fetch_assoc();
                if ($appt) {
                    $message = "Your queue status has been updated to: " . ucfirst($new_status);
                    $conn->query("INSERT INTO notifications (recipient_id, sender_id, title, message, notification_type, created_at)
                                 VALUES ({$appt['user_id']}, {$_SESSION['user_id']}, 'Queue Status Update', '$message', 'queue', NOW())");
                }
            }
            
            $_SESSION['success'] = "Queue status updated!";
        }
    }
}


$queue_stats = $conn->query("
    SELECT 
        COUNT(*) as total_queue,
        COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting_count,
        COUNT(CASE WHEN status = 'in-service' THEN 1 END) as in_service_count,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
    FROM queue_management
")->fetch_assoc();


$active_queue = $conn->query("
    SELECT qm.*, a.appointment_date, a.appointment_time, u.full_name, v.plate_number, s.service_name
    FROM queue_management qm
    JOIN appointments a ON qm.appointment_id = a.appointment_id
    JOIN users u ON a.user_id = u.user_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN services s ON a.service_id = s.service_id
    WHERE qm.status IN ('waiting', 'in-service')
    ORDER BY qm.queue_number ASC
");


$recent_notifications = $conn->query("
    SELECT n.*, u.full_name
    FROM notifications n
    LEFT JOIN users u ON n.recipient_id = u.user_id
    ORDER BY n.created_at DESC
    LIMIT 100
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid 
    .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-label { color: 
    .queue-card { background: white; padding: 15px; margin-bottom: 10px; border-radius: 8px; border-left: 4px solid 
    .queue-card.in-service { border-left-color: 
    .queue-card.waiting { border-left-color: 
    .queue-number { font-size: 28px; font-weight: bold; color: 
    .queue-info { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }
    .queue-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .status-badge.waiting { background: 
    .status-badge.in-service { background: 
    .status-badge.completed { background: 
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-success { background: 
    .btn-success:hover { background: 
    .btn-small { padding: 5px 10px; font-size: 12px; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .modal-content { background: white; margin: 5% auto; padding: 30px; border: 1px solid 
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid 
    .notification-item { background: 
    .notification-item.unread { background: 
    .notification-title { font-weight: bold; color: 
    .notification-message { color: 
    .notification-time { font-size: 12px; color: 
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid 
    .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
    .tab.active { border-bottom-color: 
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .alert-success { background: 
</style>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;"><i class="fas fa-bell"></i> Queue & Notifications</h2>
        <button class="btn btn-primary" onclick="openNotificationModal()">
            <i class="fas fa-envelope"></i> Send Notification
        </button>
    </div>
    
    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active" onclick="switchTab('queue')">Queue Management</div>
        <div class="tab" onclick="switchTab('notifications')">Notifications</div>
    </div>
    
    <!-- Queue Tab -->
    <div id="queue" class="tab-content">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: 
                <div class="stat-label">Total Queue</div>
                <div class="stat-number" style="color: 
            </div>
            
            <div class="stat-card" style="border-left-color: 
                <div class="stat-label">Waiting</div>
                <div class="stat-number" style="color: 
            </div>
            
            <div class="stat-card" style="border-left-color: 
                <div class="stat-label">In Service</div>
                <div class="stat-number" style="color: 
            </div>
            
            <div class="stat-card" style="border-left-color: 
                <div class="stat-label">Completed</div>
                <div class="stat-number" style="color: 
            </div>
        </div>
        
        <!-- Active Queue -->
        <h3 style="margin-top: 30px;">Current Queue Status</h3>
        <?php while ($queue = $active_queue->fetch_assoc()): ?>
            <div class="queue-card <?php echo $queue['status']; ?>">
                <div class="queue-header">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div class="queue-number">
                        <div>
                            <div style="font-weight: bold;"><?php echo htmlspecialchars($queue['full_name']); ?></div>
                            <small style="color: 
                        </div>
                    </div>
                    <span class="status-badge <?php echo $queue['status']; ?>">
                        <?php echo ucfirst($queue['status']); ?>
                    </span>
                </div>
                
                <div class="queue-info">
                    <div>
                        <small style="color: 
                        <div><?php echo htmlspecialchars($queue['service_name'] ?? 'Not specified'); ?></div>
                    </div>
                    <div>
                        <small style="color: 
                        <div><?php echo date('M d, Y H:i', strtotime($queue['appointment_date'] . ' ' . $queue['appointment_time'])); ?></div>
                    </div>
                </div>
                
                <div>
                    <select class="btn" style="padding: 6px 10px; background: white; border: 1px solid 
                        <option value="">Change Status...</option>
                        <option value="waiting">Waiting</option>
                        <option value="in-service">In Service</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Notifications Tab -->
    <div id="notifications" class="tab-content" style="display: none;">
        <h3>Recent Notifications</h3>
        <div style="margin-bottom: 20px;">
            <input type="text" id="notificationFilter" placeholder="Search notifications..." 
                   style="padding: 8px; border: 1px solid 
                   onkeyup="filterNotifications()">
        </div>
        
        <div id="notificationsList">
            <?php while ($notif = $recent_notifications->fetch_assoc()): ?>
                <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                    <div style="display: flex; justify-content: space-between;">
                        <div style="flex: 1;">
                            <div class="notification-title">
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 3px; background: 
                                    <?php echo ucfirst($notif['notification_type']); ?>
                                </span>
                                <?php echo htmlspecialchars($notif['title']); ?>
                            </div>
                            <div class="notification-message">
                                <?php echo htmlspecialchars($notif['message']); ?>
                            </div>
                            <div class="notification-time">
                                Sent to: <strong><?php echo htmlspecialchars($notif['full_name'] ?? 'System'); ?></strong> 
                                on <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                            </div>
                        </div>
                        <?php if (!$notif['is_read']): ?>
                            <div style="margin-left: 10px;">
                                <span style="display: inline-block; width: 10px; height: 10px; background: 
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Send Notification Modal -->
<div id="notificationModal" class="modal">
    <div class="modal-content">
        <h3>Send Notification</h3>
        <form method="POST">
            <input type="hidden" name="action" value="send_notification">
            
            <div class="form-group">
                <label>Recipient:</label>
                <select name="recipient_id" required>
                    <option value="">Select Client</option>
                    <?php
                    $clients = $conn->query("SELECT user_id, full_name FROM users WHERE role = 'client' AND status = 'active' ORDER BY full_name");
                    while ($client = $clients->fetch_assoc()) {
                        echo '<option value="' . $client['user_id'] . '">' . htmlspecialchars($client['full_name']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notification Type:</label>
                <select name="notification_type" required>
                    <option value="system">System</option>
                    <option value="appointment">Appointment</option>
                    <option value="payment">Payment</option>
                    <option value="queue">Queue</option>
                    <option value="service">Service</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Title:</label>
                <input type="text" name="title" required>
            </div>
            
            <div class="form-group">
                <label>Message:</label>
                <textarea name="message" rows="4" required></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Send</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    document.getElementById(tabName).style.display = 'block';
    
    const tabButtons = document.querySelectorAll('.tab');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}

function openNotificationModal() {
    document.getElementById('notificationModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function updateQueueStatus(queueId, status) {
    if (status) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="update_queue_status"><input type="hidden" name="queue_id" value="' + queueId + '"><input type="hidden" name="status" value="' + status + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function filterNotifications() {
    const filter = document.getElementById('notificationFilter').value.toLowerCase();
    const items = document.querySelectorAll('.notification-item');
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(filter) ? 'block' : 'none';
    });
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

