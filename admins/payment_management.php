<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Payment Management';
$page_icon = 'fas fa-credit-card';
include __DIR__ . '/includes/admin_layout_header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_payment') {
        $payment_id = intval($_POST['payment_id']);
        $new_status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE payments SET status = ?, updated_at = NOW() WHERE payment_id = ?");
        $stmt->bind_param('si', $new_status, $payment_id);
        
        if ($stmt->execute()) {
            
            $conn->query("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values, status) 
                         VALUES ({$_SESSION['user_id']}, 'UPDATE_PAYMENT', 'payments', $payment_id, 
                         JSON_OBJECT('status', '$new_status'), 'success')");
            
            $_SESSION['success'] = "Payment status updated successfully!";
        }
    }
}


$payment_stats = $conn->query("
    SELECT 
        COUNT(*) as total_payments,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
        SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_completed,
        SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending
    FROM payments
")->fetch_assoc();


$payments = $conn->query("
    SELECT p.*, 
           u.full_name, u.email,
           a.appointment_date, s.service_name,
           sh.service_date
    FROM payments p
    JOIN users u ON p.user_id = u.user_id
    LEFT JOIN appointments a ON p.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN service_history sh ON p.history_id = sh.history_id
    ORDER BY p.created_at DESC
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid 
    .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-label { color: 
    .payments-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .payments-table thead { background: 
    .payments-table th, .payments-table td { padding: 15px; text-align: left; border-bottom: 1px solid 
    .payments-table th { font-weight: bold; color: 
    .payments-table tbody tr:hover { background: 
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .status-badge.completed { background: 
    .status-badge.pending { background: 
    .status-badge.failed { background: 
    .status-badge.refunded { background: 
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-small { padding: 5px 10px; font-size: 12px; }
    .filter-bar { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .filter-group { display: flex; gap: 15px; flex-wrap: wrap; }
    .filter-group input, .filter-group select { padding: 8px; border: 1px solid 
    .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
    .alert-success { background: 
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .modal-content { background: white; margin: 10% auto; padding: 20px; border: 1px solid 
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid 
</style>

<div class="container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <h2 style="margin-bottom: 20px;"><i class="fas fa-credit-card"></i> Payment Management</h2>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Completed Payments</div>
            <div class="stat-number" style="color: 
            <small><?php echo $payment_stats['completed_count'] ?? 0; ?> transactions</small>
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Pending Payments</div>
            <div class="stat-number" style="color: 
            <small><?php echo $payment_stats['pending_count'] ?? 0; ?> transactions</small>
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Failed Payments</div>
            <div class="stat-number" style="color: 
            <small>transactions</small>
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Transactions</div>
            <div class="stat-number" style="color: 
            <small>all payments</small>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-bar">
        <div class="filter-group">
            <select id="filterStatus" onchange="filterPayments()">
                <option value="">All Status</option>
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
            </select>
            <input type="date" id="filterDate" placeholder="Filter by date" onchange="filterPayments()">
            <input type="text" id="filterCustomer" placeholder="Search customer..." onchange="filterPayments()">
            <input type="text" id="filterAmount" placeholder="Filter by amount" onchange="filterPayments()">
        </div>
    </div>
    
    <!-- Payments Table -->
    <table class="payments-table">
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Reference</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="paymentsBody">
            <?php while ($payment = $payments->fetch_assoc()): ?>
                <tr class="payment-row" 
                    data-status="<?php echo $payment['status']; ?>" 
                    data-customer="<?php echo strtolower($payment['full_name']); ?>"
                    data-amount="<?php echo $payment['amount']; ?>"
                    data-date="<?php echo $payment['created_at']; ?>">
                    <td><strong>
                    <td>
                        <div><?php echo htmlspecialchars($payment['full_name']); ?></div>
                        <small style="color: 
                    </td>
                    <td><strong>â‚±<?php echo number_format($payment['amount'], 2); ?></strong></td>
                    <td><?php echo ucfirst($payment['payment_method']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $payment['status']; ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($payment['reference_number'] ?? '-'); ?></td>
                    <td>
                        <button class="btn btn-primary btn-small" onclick="changePaymentStatus(<?php echo $payment['payment_id']; ?>, '<?php echo $payment['status']; ?>')">
                            Update
                        </button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Status Change Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <h3>Update Payment Status</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_payment">
            <input type="hidden" name="payment_id" id="paymentId">
            
            <div class="form-group">
                <label for="newStatus">New Status:</label>
                <select name="status" id="newStatus" required>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<script>
function changePaymentStatus(paymentId, currentStatus) {
    document.getElementById('paymentId').value = paymentId;
    document.getElementById('newStatus').value = currentStatus;
    document.getElementById('statusModal').style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function filterPayments() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const date = document.getElementById('filterDate').value;
    const customer = document.getElementById('filterCustomer').value.toLowerCase();
    const amount = document.getElementById('filterAmount').value;
    
    const rows = document.querySelectorAll('.payment-row');
    rows.forEach(row => {
        let show = true;
        
        if (status && row.dataset.status !== status) show = false;
        if (customer && !row.dataset.customer.includes(customer)) show = false;
        if (amount && row.dataset.amount !== amount) show = false;
        if (date && !row.dataset.date.startsWith(date)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

window.onclick = function(event) {
    let modal = document.getElementById('statusModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

