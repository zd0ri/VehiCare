<?php
session_start();
require_once __DIR__ . '/../includes/config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];


$queue_query = "SELECT q.*, a.appointment_date, a.appointment_time, a.appointment_type,
                       v.vehicle_type, v.make, v.model, v.plate_number,
                       s.service_name
                FROM queue_management q
                JOIN appointments a ON q.appointment_id = a.appointment_id
                JOIN vehicles v ON a.vehicle_id = v.vehicle_id
                LEFT JOIN services s ON a.service_id = s.service_id
                WHERE a.user_id = $user_id AND (q.status = 'waiting' OR q.status = 'in-service')
                ORDER BY q.queue_number ASC";

$result = $conn->query($queue_query);
$queue_items = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $queue_items[] = $row;
    }
}


$waiting_count = count(array_filter($queue_items, fn($item) => $item['status'] === 'waiting'));

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, 
        <h1 style="margin: 0 0 10px 0;"><i class="fas fa-hourglass-start"></i> Queue Status</h1>
        <p style="margin: 0; opacity: 0.9;">Check your position in the service queue</p>
    </div>

    <!-- Queue Info -->
    <?php if (count($queue_items) > 0): ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid 
            <p style="margin: 0; color: 
            <h3 style="margin: 10px 0 0 0; color: 
                <?php 
                    $your_position = null;
                    foreach ($queue_items as $idx => $item) {
                        if ($item['status'] === 'waiting') {
                            $your_position = $idx + 1;
                            break;
                        }
                    }
                    echo $your_position ? 'Position #' . $your_position : 'Not in queue';
                ?>
            </h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #2d5a7b;">
            <p style="margin: 0; color: #666;">Estimated wait time</p>
            <h3 style="margin: 10px 0 0 0; color: #2d5a7b;">Checking your position...</h3>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #2d5a7b;">
            <p style="margin: 0; color: #666;">Current service time</p>
            <h3 style="margin: 10px 0 0 0; color: #2d5a7b;">
                <?php 
                    $in_service = current(array_filter($queue_items, fn($item) => $item['status'] === 'in-service'));
                    $est_time = $in_service['estimated_wait_time'] ?? 0;
                    echo $est_time > 0 ? $est_time . ' min' : 'TBA';
                ?>
            </h3>
        </div>
    </div>

    <!-- Queue List -->
    <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">
        <div style="background: #f8f9fa; padding: 20px; border-bottom: 1px solid #e0e0e0;">
            <h5 style="margin: 0; color: #333;">Queue Details</h5>
        </div>

        <div style="padding: 20px;">
            <?php foreach ($queue_items as $item): ?>
            <div style="background: <?php echo $item['status'] === 'in-service' ? '#e8f5e9' : '#f5f5f5'; ?>; 
                        border-left: 4px solid <?php echo $item['status'] === 'in-service' ? '#28a745' : '#2d5a7b'; ?>;
                        padding: 20px; margin-bottom: 15px; border-radius: 8px;">
                <div style="display: grid; grid-template-columns: 100px 1fr 150px; gap: 20px; align-items: center;">
                    <div style="text-align: center;">
                        <h2 style="margin: 0; color: <?php echo $item['status'] === 'in-service' ? '#28a745' : '#2d5a7b'; ?>;">
                            <?php echo isset($item['queue_number']) ? $item['queue_number'] : '–'; ?>
                        </h2>
                        <small style="color: #666;"><?php echo isset($item['queue_position']) ? 'Position #' . $item['queue_position'] : 'Not in queue'; ?></small>
                    </div>
                    <div>
                        <h5 style="margin: 0 0 5px 0; color: #333;">
                            <?php echo htmlspecialchars($item['vehicle_type'] . ' - ' . $item['make'] . ' ' . $item['model']); ?>
                        </h5>
                        <p style="margin: 0 0 5px 0; color: #666;">
                            <strong>Plate:</strong> <?php echo htmlspecialchars($item['plate_number']); ?>
                        </p>
                        <p style="margin: 0 0 5px 0; color: #666;">
                            <strong>Service:</strong> <?php echo $item['service_name'] ? htmlspecialchars($item['service_name']) : 'General Service'; ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <span style="background: <?php echo $item['status'] === 'in-service' ? '#28a745' : '#ffc107'; ?>; color: white; padding: 8px 15px; border-radius: 4px; font-weight: 600;"><?php echo strtoupper($item['status']); ?></span>
                        <?php if ($item['status'] === 'in-service' && $item['estimated_wait_time']): ?>
                        <p style="margin: 10px 0 0 0; color: #666;">
                            <strong>Est. Time:</strong> <?php echo $item['estimated_wait_time']; ?> min
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tips -->
    <div style="background: 
        <h6 style="margin: 0 0 10px 0; color: 
        <ul style="margin: 0; color: 
            <li>Arrive 10-15 minutes before your scheduled appointment time</li>
            <li>Your queue position updates automatically</li>
            <li>If you need to leave, please notify staff immediately</li>
        </ul>
    </div>

    <?php else: ?>
    <div style="background: white; padding: 60px 20px; border-radius: 12px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        <i class="fas fa-check-circle" style="font-size: 60px; color: 
        <h4 style="color: 
        <p style="color: 
        <a href="/vehicare_db/client/appointments.php" class="btn btn-primary" style="background: 
            <i class="fas fa-calendar-plus"></i> View Appointments
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>

