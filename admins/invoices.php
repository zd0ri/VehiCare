<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /vehicare_db/login.php");
    exit;
}

// Get all invoices
$invoices = $conn->query("
    SELECT i.*, u.full_name, s.service_name, a.appointment_date, a.client_id
    FROM invoices i
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    LEFT JOIN users u ON a.client_id = u.user_id
    LEFT JOIN services s ON a.service_id = s.service_id
    ORDER BY i.invoice_date DESC
");

$page_title = "Invoices";
$page_icon = "fas fa-receipt";
include __DIR__ . '/includes/admin_layout_header.php';
?>

<!-- Page Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Invoice Management</h3>
        <button class="btn btn-primary" onclick="alert('Generate Invoice feature coming soon')">
            <i class="fas fa-plus"></i> Generate Invoice
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Date</th>
                    <th>Labor Cost</th>
                    <th>Parts Cost</th>
                    <th>Grand Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoices && $invoices->num_rows > 0): ?>
                    <?php while($invoice = $invoices->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $invoice['invoice_id']; ?></td>
                        <td><?php echo htmlspecialchars($invoice['full_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['service_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($invoice['invoice_date'] ?? 'N/A'); ?></td>
                        <td>₱<?php echo number_format($invoice['total_labor'] ?? 0, 2); ?></td>
                        <td>₱<?php echo number_format($invoice['total_parts'] ?? 0, 2); ?></td>
                        <td><strong>₱<?php echo number_format($invoice['grand_total'] ?? 0, 2); ?></strong></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info" onclick="viewInvoice(<?php echo $invoice['invoice_id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="printInvoice(<?php echo $invoice['invoice_id']; ?>)">
                                <i class="fas fa-print"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteInvoice(<?php echo $invoice['invoice_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No invoices found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function viewInvoice(id) {
    alert('View invoice ' + id);
}

function printInvoice(id) {
    alert('Print invoice ' + id);
}

function deleteInvoice(id) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        alert('Delete invoice ' + id);
    }
}
</script>

<?php include __DIR__ . '/includes/admin_layout_footer.php'; ?>
