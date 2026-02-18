<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$queues = $conn->query("
    SELECT q.*, a.appointment_date, a.appointment_time, u.full_name as client_name, s.service_name
    FROM queue q
    JOIN appointments a ON q.appointment_id = a.appointment_id
    JOIN users u ON a.client_id = u.user_id
    LEFT JOIN services s ON a.service_id = s.service_id
    ORDER BY q.queue_number ASC
");

$page_title = "Queue Management";
$page_icon = "fas fa-list-ol";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Service Queue Management</h3>
        <button class="btn btn-primary" onclick="alert('Add to Queue feature coming soon')">
            <i class="fas fa-plus"></i> Add to Queue
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Queue #</th>
                    <th>Client</th>
                    <th>Appointment Date</th>
                    <th>Appointment Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($queues && $queues->num_rows > 0): ?>
                    <?php while($queue = $queues->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $queue['queue_number']; ?></td>
                        <td><?php echo htmlspecialchars($queue['client_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($queue['appointment_date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($queue['appointment_time'] ?? 'N/A'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $queue['status'] == 'completed' ? 'success' : ($queue['status'] == 'in-progress' ? 'warning' : 'secondary'); ?>">
                                <?php echo htmlspecialchars($queue['status'] ?? 'pending'); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-success" onclick="completeQueue(<?php echo $queue['queue_id']; ?>)">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="removeFromQueue(<?php echo $queue['queue_id']; ?>)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No items in queue</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function completeQueue(id) {
    if (confirm('Mark this queue item as completed?')) {
        alert('Queue item ' + id + ' completed');
    }
}

function removeFromQueue(id) {
    if (confirm('Remove this item from the queue?')) {
        alert('Remove queue item ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
