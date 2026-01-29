<?php

session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$message = '';
$error = '';


$queue_query = "SELECT q.*, a.appointment_date, a.appointment_time, a.appointment_type,
                       u.full_name as customer_name, u.email,
                       v.vehicle_type, v.make, v.model, v.plate_number,
                       s.service_name, st.full_name as staff_name
                FROM queue_management q
                JOIN appointments a ON q.appointment_id = a.appointment_id
                JOIN users u ON a.user_id = u.user_id
                JOIN vehicles v ON a.vehicle_id = v.vehicle_id
                LEFT JOIN services s ON a.service_id = s.service_id
                LEFT JOIN users st ON a.assigned_to = st.user_id
                WHERE q.status IN ('waiting', 'in-service')
                ORDER BY q.queue_number ASC";

$result = $conn->query($queue_queue);
$queue_items = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $queue_items[] = $row;
    }
}


if (isset($_POST['action'])) {
    $queue_id = intval($_POST['queue_id']);
    $new_status = trim($_POST['new_status']);
    
    $allowed_statuses = ['waiting', 'in-service', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        $update = "UPDATE queue_management SET status = '$new_status' WHERE queue_id = $queue_id";
        if ($conn->query($update)) {
            $message = "Queue status updated!";
        } else {
            $error = "Failed to update status.";
        }
    }
}

$page_title = 'Queue Management';
$page_icon = 'fas fa-hourglass-start';
include __DIR__ . '/includes/admin_layout_header.php';
?>

<div style="flex: 1; overflow-y: auto; padding: 30px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 5px 0; font-size: 28px;"><i class="fas fa-hourglass-start"></i> Queue Management</h1>
        <p style="margin: 0; opacity: 0.9;">Manage service queue and process customers</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
    <div style="background: 
        <i class="fas fa-check-circle"></i> <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div style="background: 
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Queue Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($queue_items, fn($q) => $q['status'] === 'waiting')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count(array_filter($queue_items, fn($q) => $q['status'] === 'in-service')); ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php echo count($queue_items); ?>
            </h3>
        </div>
    </div>

    <!-- Queue List -->
    <?php if (count($queue_items) > 0): ?>
    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #e0e0e0;">
            <h5 style="margin: 0; color: #333;">Queue Status</h5>
        </div>

        <?php foreach ($queue_items as $item): ?>
        <div style="padding: 20px; border-bottom: 1px solid #e0e0e0;">
            <div style="display: grid; grid-template-columns: 80px 1fr auto; gap: 20px; align-items: center;">
                <!-- Queue Number -->
                <div style="text-align: center; padding: 15px; background: <?php echo $item['status'] === 'in-service' ? '#28a745' : '#f0f0f0'; ?>; border-radius: 8px;">
                    <h2 style="margin: 0; font-size: 32px; color: <?php echo $item['status'] === 'in-service' ? 'white' : '#333'; ?>;"><?php echo $item['queue_position']; ?></h2>
                    <small style="color: <?php echo $item['status'] === 'in-service' ? 'rgba(255,255,255,0.8)' : '#666'; ?>;">Queue No.</small>
                </div>

                <!-- Details -->
                <div>
                    <h5 style="margin: 0 0 8px 0; color: #333;">
                        <?php echo htmlspecialchars($item['customer_name']); ?>
                        <span style="background: <?php echo $item['status'] === 'in-service' ? '#28a745' : '#ffc107'; ?>; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px; float: right;">
                            <?php echo strtoupper($item['status']); ?>
                        </span>
                    </h5>
                    <p style="margin: 0 0 5px 0; color: #666;">
                        <strong>Vehicle:</strong> <?php echo htmlspecialchars($item['vehicle_type'] . ' - ' . $item['make'] . ' ' . $item['model']); ?> (<?php echo htmlspecialchars($item['plate_number']); ?>)
                    </p>
                    <p style="margin: 0 0 5px 0; color: #666;">
                        <strong>Service:</strong> <?php echo $item['service_name'] ? htmlspecialchars($item['service_name']) : 'General Service'; ?>
                    </p>
                    <p style="margin: 0; color: #666;">
                        <strong>Scheduled:</strong> <?php echo date('M d, Y h:i A', strtotime($item['appointment_date'] . ' ' . $item['appointment_time'])); ?>
                    </p>
                </div>

                <!-- Actions -->
                <div style="text-align: right;">
                    <form method="POST" action="" style="margin: 0;">
                        <input type="hidden" name="queue_id" value="<?php echo $item['queue_id']; ?>">
                        <?php if ($item['status'] === 'waiting'): ?>
                        <button type="submit" name="action" value="start_service" onclick="this.form.new_status = 'in-service'; this.form.submit();" class="btn" style="background: 
                            <i class="fas fa-play"></i> Start Service
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($item['status'] === 'in-service'): ?>
                        <button type="submit" name="action" value="complete_service" onclick="this.form.new_status = 'completed'; this.form.submit();" class="btn" style="background: 
                            <i class="fas fa-check"></i> Complete Service
                        </button>
                        <?php endif; ?>

                        <button type="submit" name="action" value="cancel" onclick="this.form.new_status = 'cancelled'; return confirm('Cancel this queue item?'); this.form.submit();" class="btn" style="background: 
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <input type="hidden" name="new_status" value="">
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="background: white; padding: 60px 20px; border-radius: 8px; text-align: center;">
        <i class="fas fa-hourglass-end" style="font-size: 48px; color: 
        <h4 style="color: 
        <p style="color: 
    </div>
    <?php endif; ?>
</div>

<style>
    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
</style>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

