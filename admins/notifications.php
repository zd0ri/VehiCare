<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Handle sending notifications
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notif'])) {
    $user_id = intval($_POST['user_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $type = $conn->real_escape_string($_POST['type']);
    
    try {
        $conn->query("INSERT INTO notifications (user_id, title, message, type, created_at) VALUES ($user_id, '$title', '$message', '$type', NOW())");
        $_SESSION['success'] = "Notification sent successfully!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    header("Location: /vehicare_db/admins/notifications.php");
    exit;
}

// Fetch notifications with error handling
try {
    $notifications = $conn->query("SELECT n.*, u.full_name FROM notifications n JOIN users u ON n.user_id = u.user_id ORDER BY n.created_at DESC LIMIT 50");
} catch (Exception $e) {
    $notifications = false;
    $error_message = "Notifications table not found. Please run the database setup.";
}

try {
    $clients = $conn->query("SELECT user_id, full_name FROM users WHERE role='client'");
} catch (Exception $e) {
    $clients = false;
}

$page_title = "Notifications";
$page_icon = "fas fa-bell";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Notifications Management</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendModal">
            <i class="fas fa-plus"></i> Send Notification
        </button>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-warning">
            <strong>Database Issue:</strong> <?php echo $error_message; ?>
            <br><a href="/vehicare_db/create_missing_tables.php" class="btn btn-sm btn-primary mt-2">Fix Database</a>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Recipient</th>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($notifications && $notifications->num_rows > 0): ?>
                    <?php while($notification = $notifications->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $notification['notification_id']; ?></td>
                        <td><?php echo htmlspecialchars($notification['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($notification['title'] ?? 'No Title'); ?></td>
                        <td><?php echo htmlspecialchars(substr($notification['message'], 0, 50)) . '...'; ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $notification['type'] === 'success' ? 'success' : 
                                    ($notification['type'] === 'warning' ? 'warning' : 'info'); 
                            ?>">
                                <?php echo ucfirst($notification['type']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo isset($notification['is_read']) && $notification['is_read'] ? 'success' : 'secondary'; ?>">
                                <?php echo isset($notification['is_read']) && $notification['is_read'] ? 'Read' : 'Unread'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No notifications found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Send Notification Modal -->
<div class="modal fade" id="sendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="send_notif" value="1">
                    
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Select User</option>
                            <?php if ($clients && $clients->num_rows > 0): ?>
                                <?php while($client = $clients->fetch_assoc()): ?>
                                <option value="<?php echo $client['user_id']; ?>"><?php echo htmlspecialchars($client['full_name']); ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control" required>
                            <option value="info">Info</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
