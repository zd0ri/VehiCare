<?php

session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$page_title = 'Billing & Invoices';
$page_icon = 'fas fa-file-invoice-dollar';
include __DIR__ . '/includes/admin_layout_header.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate_invoice') {
        $appointment_id = intval($_POST['appointment_id']);
        $subtotal = floatval($_POST['subtotal']);
        $tax = floatval($_POST['tax']);
        $total_amount = $subtotal + $tax;
        $due_date = $_POST['due_date'];
        
        
        $appt = $conn->query("SELECT user_id FROM appointments WHERE appointment_id = $appointment_id")->fetch_assoc();
        $user_id = $appt['user_id'];
        
        
        $invoice_number = 'INV-' . date('Ym') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO invoices (appointment_id, user_id, invoice_number, invoice_date, due_date, subtotal, tax, total_amount, status)
                               VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, 'issued')");
        $stmt->bind_param('isisdd', $appointment_id, $user_id, $invoice_number, $due_date, $subtotal, $tax);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Invoice generated successfully! Invoice #" . $invoice_id . " created.";
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_payment_status') {
        $invoice_id = intval($_POST['invoice_id']);
        $paid_amount = floatval($_POST['paid_amount']);
        
        
        $invoice = $conn->query("SELECT total_amount FROM invoices WHERE invoice_id = $invoice_id")->fetch_assoc();
        $total = $invoice['total_amount'];
        
        $status = $paid_amount >= $total ? 'paid' : 'issued';
        
        $stmt = $conn->prepare("UPDATE invoices SET paid_amount = ?, status = ?, updated_at = NOW() WHERE invoice_id = ?");
        $stmt->bind_param('dsi', $paid_amount, $status, $invoice_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Payment status updated!";
        }
    }
}


$billing_stats = $conn->query("
    SELECT 
        COUNT(*) as total_invoices,
        COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count,
        COUNT(CASE WHEN status = 'issued' THEN 1 END) as issued_count,
        COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count,
        SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN status != 'paid' THEN total_amount - paid_amount ELSE 0 END) as outstanding_balance
    FROM invoices
")->fetch_assoc();


$invoices = $conn->query("
    SELECT i.*, u.full_name, u.email, a.appointment_date
    FROM invoices i
    JOIN users u ON i.user_id = u.user_id
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    ORDER BY i.created_at DESC
");
?>

<style>
    .container { max-width: 1200px; margin: 0 auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-left: 4px solid 
    .stat-number { font-size: 32px; font-weight: bold; margin: 10px 0; }
    .stat-label { color: 
    .invoices-table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .invoices-table thead { background: 
    .invoices-table th, .invoices-table td { padding: 15px; text-align: left; border-bottom: 1px solid 
    .invoices-table tbody tr:hover { background: 
    .status-badge { display: inline-block; padding: 6px 12px; border-radius: 5px; font-size: 12px; font-weight: bold; }
    .status-badge.paid { background: 
    .status-badge.issued { background: 
    .status-badge.overdue { background: 
    .status-badge.draft { background: 
    .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 13px; }
    .btn-primary { background: 
    .btn-primary:hover { background: 
    .btn-small { padding: 5px 10px; font-size: 12px; }
    .filter-bar { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .filter-group { display: flex; gap: 15px; flex-wrap: wrap; }
    .filter-group input, .filter-group select { padding: 8px; border: 1px solid 
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); }
    .modal-content { background: white; margin: 5% auto; padding: 30px; border: 1px solid 
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid 
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
        <h2 style="margin: 0;"><i class="fas fa-file-invoice-dollar"></i> Billing & Invoices</h2>
        <button class="btn btn-primary" onclick="openGenerateInvoiceModal()">
            <i class="fas fa-plus"></i> Generate Invoice
        </button>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Invoices</div>
            <div class="stat-number" style="color: 
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Total Collected</div>
            <div class="stat-number" style="color: 
            <small><?php echo $billing_stats['paid_count']; ?> paid invoices</small>
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Outstanding Balance</div>
            <div class="stat-number" style="color: 
            <small><?php echo $billing_stats['issued_count']; ?> pending</small>
        </div>
        
        <div class="stat-card" style="border-left-color: 
            <div class="stat-label">Overdue</div>
            <div class="stat-number" style="color: 
            <small>invoices</small>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-bar">
        <div class="filter-group">
            <select id="filterStatus" onchange="filterInvoices()">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="issued">Issued</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input type="date" id="filterDate" onchange="filterInvoices()">
            <input type="text" id="filterCustomer" placeholder="Search customer..." onchange="filterInvoices()">
        </div>
    </div>
    
    <!-- Invoices Table -->
    <table class="invoices-table">
        <thead>
            <tr>
                <th>Invoice 
                <th>Customer</th>
                <th>Invoice Date</th>
                <th>Due Date</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="invoicesBody">
            <?php while ($invoice = $invoices->fetch_assoc()): 
                $balance = $invoice['total_amount'] - $invoice['paid_amount'];
            ?>
                <tr class="invoice-row" 
                    data-status="<?php echo $invoice['status']; ?>"
                    data-customer="<?php echo strtolower($invoice['full_name']); ?>"
                    data-date="<?php echo $invoice['invoice_date']; ?>">
                    <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                    <td>
                        <div><?php echo htmlspecialchars($invoice['full_name']); ?></div>
                        <small style="color: 
                    </td>
                    <td><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></td>
                    <td><?php echo $invoice['due_date'] ? date('M d, Y', strtotime($invoice['due_date'])) : '-'; ?></td>
                    <td><strong>â‚±<?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
                    <td>â‚±<?php echo number_format($invoice['paid_amount'], 2); ?></td>
                    <td><?php echo $balance > 0 ? 'â‚±' . number_format($balance, 2) : 'âœ“ Paid'; ?></td>
                    <td>
                        <span class="status-badge <?php echo $invoice['status']; ?>">
                            <?php echo ucfirst($invoice['status']); ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-small" onclick="viewInvoice(<?php echo $invoice['invoice_id']; ?>)">
                            View
                        </button>
                        <?php if ($invoice['status'] !== 'paid'): ?>
                            <button class="btn btn-small" style="background: 
                                Pay
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Generate Invoice Modal -->
<div id="generateInvoiceModal" class="modal">
    <div class="modal-content">
        <h3>Generate Invoice</h3>
        <form method="POST">
            <input type="hidden" name="action" value="generate_invoice">
            
            <div class="form-group">
                <label>Appointment:</label>
                <select name="appointment_id" required>
                    <option value="">Select Appointment</option>
                    <?php
                    $appts = $conn->query("
                        SELECT a.appointment_id, u.full_name, a.appointment_date, s.service_name
                        FROM appointments a
                        JOIN users u ON a.user_id = u.user_id
                        LEFT JOIN services s ON a.service_id = s.service_id
                        WHERE a.status IN ('completed', 'in-progress')
                        ORDER BY a.appointment_date DESC
                    ");
                    while ($a = $appts->fetch_assoc()) {
                        echo '<option value="' . $a['appointment_id'] . '">' . htmlspecialchars($a['full_name']) . ' - ' . $a['service_name'] . ' (' . date('M d, Y', strtotime($a['appointment_date'])) . ')</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Subtotal (â‚±):</label>
                <input type="number" name="subtotal" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Tax (â‚±):</label>
                <input type="number" name="tax" step="0.01" value="0">
            </div>
            
            <div class="form-group">
                <label>Due Date:</label>
                <input type="date" name="due_date" required>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Generate</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<!-- Payment Recording Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <h3>Record Payment</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_payment_status">
            <input type="hidden" name="invoice_id" id="invoiceId">
            
            <div class="form-group">
                <label>Total Amount Due (â‚±):</label>
                <input type="text" id="totalDue" readonly style="background: 
            </div>
            
            <div class="form-group">
                <label>Previously Paid (â‚±):</label>
                <input type="text" id="alreadyPaid" readonly style="background: 
            </div>
            
            <div class="form-group">
                <label>Amount Paid (â‚±):</label>
                <input type="number" name="paid_amount" step="0.01" required id="paidAmount">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Record Payment</button>
                <button type="button" class="btn" style="background: 
            </div>
        </form>
    </div>
</div>

<script>
function openGenerateInvoiceModal() {
    document.getElementById('generateInvoiceModal').style.display = 'block';
}

function recordPayment(invoiceId, totalDue, alreadyPaid) {
    document.getElementById('invoiceId').value = invoiceId;
    document.getElementById('totalDue').value = totalDue.toFixed(2);
    document.getElementById('alreadyPaid').value = alreadyPaid.toFixed(2);
    document.getElementById('paidAmount').value = totalDue.toFixed(2);
    document.getElementById('paymentModal').style.display = 'block';
}

function viewInvoice(invoiceId) {
    window.open('/vehicare_db/admins/view_invoice.php?id=' + invoiceId, '_blank');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function filterInvoices() {
    const status = document.getElementById('filterStatus').value;
    const date = document.getElementById('filterDate').value;
    const customer = document.getElementById('filterCustomer').value.toLowerCase();
    
    const rows = document.querySelectorAll('.invoice-row');
    rows.forEach(row => {
        let show = true;
        
        if (status && row.dataset.status !== status) show = false;
        if (date && row.dataset.date !== date) show = false;
        if (customer && !row.dataset.customer.includes(customer)) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>

