<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Fetch payments with fallback to orders relationship
try {
    // Try the new structure first (with appointment_id and client_id in payments table)
    $paymentsQuery = $conn->query("
      SELECT p.*, 
             COALESCE(p.appointment_id, a2.appointment_id) as appointment_id,
             COALESCE(u.full_name, u2.full_name, 'Unknown Client') as full_name,
             COALESCE(s.service_name, 'No Service') as service_name
      FROM payments p
      LEFT JOIN appointments a ON p.appointment_id = a.appointment_id
      LEFT JOIN users u ON p.client_id = u.user_id
      LEFT JOIN orders o ON p.order_id = o.order_id
      LEFT JOIN users u2 ON o.client_id = u2.user_id
      LEFT JOIN appointments a2 ON u2.user_id = a2.client_id
      LEFT JOIN services s ON COALESCE(a.service_id, a2.service_id) = s.service_id
      ORDER BY p.payment_date DESC
    ");
} catch (Exception $e) {
    // Fallback to orders-based relationship if new columns don't exist yet
    $paymentsQuery = $conn->query("
      SELECT p.*, o.order_id, u.full_name, 'N/A' as service_name
      FROM payments p
      LEFT JOIN orders o ON p.order_id = o.order_id
      LEFT JOIN users u ON o.client_id = u.user_id
      ORDER BY p.payment_date DESC
    ");
}

$page_title = "Payments";
$page_icon = "fas fa-credit-card";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Payment Management</h3>
        <button class="btn btn-primary" onclick="alert('Add payment feature coming soon')">
            <i class="fas fa-plus"></i> Add Payment
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                    <?php if ($paymentsQuery && $paymentsQuery->num_rows > 0): ?>
                        <?php while ($payment = $paymentsQuery->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $payment['payment_id']; ?></strong></td>
                            <td><?php echo htmlspecialchars($payment['full_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($payment['service_name'] ?? 'N/A'); ?></td>
                            <td><strong>$<?php echo number_format($payment['amount'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <span class="badge bg-success">Completed</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No payments found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
