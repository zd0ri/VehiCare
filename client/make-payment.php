<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$invoice_id = $_GET['invoice'] ?? null;

if (!$invoice_id) {
    header("Location: /vehicare_db/client/invoices.php");
    exit;
}

// Get invoice details
$invoice_query = "
    SELECT i.*, a.appointment_date, a.appointment_time,
           s.service_name, v.plate_number, v.car_brand, v.car_model,
           u.first_name, u.last_name, u.email,
           SUM(p.amount) as total_paid
    FROM invoices i
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN users u ON i.client_id = u.user_id
    LEFT JOIN payments p ON i.invoice_id = p.invoice_id AND p.payment_status = 'completed'
    WHERE i.invoice_id = ? AND i.client_id = ?
    GROUP BY i.invoice_id
";

$stmt = $conn->prepare($invoice_query);
$stmt->bind_param("ii", $invoice_id, $client_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    header("Location: /vehicare_db/client/invoices.php");
    exit;
}

// Calculate remaining balance
$total_paid = $invoice['total_paid'] ?? 0;
$remaining_balance = $invoice['grand_total'] - $total_paid;

if ($remaining_balance <= 0 || $invoice['payment_status'] === 'paid') {
    header("Location: /vehicare_db/client/invoice-details.php?id=" . $invoice_id);
    exit;
}

// Handle payment form submission
$success_message = '';
$error_message = '';

if ($_POST) {
    $payment_method = $_POST['payment_method'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $reference_number = $_POST['reference_number'] ?? '';
    $notes = $_POST['notes'] ?? '';

    // Validation
    if (empty($payment_method)) {
        $error_message = "Please select a payment method.";
    } elseif ($amount <= 0 || $amount > $remaining_balance) {
        $error_message = "Please enter a valid payment amount between ₱1.00 and ₱" . number_format($remaining_balance, 2) . ".";
    } else {
        // Insert payment record
        $payment_status = ($payment_method === 'cash') ? 'pending' : 'completed';
        
        $insert_payment = "
            INSERT INTO payments (client_id, invoice_id, amount, payment_method, payment_status, payment_date, reference_number, notes)
            VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)
        ";
        
        $stmt = $conn->prepare($insert_payment);
        $stmt->bind_param("iidssss", $client_id, $invoice_id, $amount, $payment_method, $payment_status, $reference_number, $notes);
        
        if ($stmt->execute()) {
            $payment_id = $conn->insert_id;
            
            // Update invoice payment status
            $new_total_paid = $total_paid + $amount;
            $new_payment_status = 'partial';
            
            if ($new_total_paid >= $invoice['grand_total']) {
                $new_payment_status = 'paid';
            }
            
            $update_invoice = "UPDATE invoices SET payment_status = ? WHERE invoice_id = ?";
            $stmt2 = $conn->prepare($update_invoice);
            $stmt2->bind_param("si", $new_payment_status, $invoice_id);
            $stmt2->execute();
            
            // Add to client activity log
            $activity_description = "Made payment of ₱" . number_format($amount, 2) . " for Invoice #" . str_pad($invoice_id, 6, '0', STR_PAD_LEFT);
            $log_activity = "INSERT INTO client_activity_logs (client_id, activity_type, activity_description) VALUES (?, 'payment', ?)";
            $stmt3 = $conn->prepare($log_activity);
            $stmt3->bind_param("is", $client_id, $activity_description);
            $stmt3->execute();
            
            // Redirect to success page
            header("Location: /vehicare_db/client/payment-success.php?payment=" . $payment_id);
            exit;
        } else {
            $error_message = "Failed to process payment. Please try again.";
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - VehiCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8f9fa;
        }

        .payment-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .invoice-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .invoice-number {
            font-size: 1.5em;
            font-weight: 700;
            color: #2c3e50;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: capitalize;
            background: #fff3cd;
            color: #856404;
        }

        .invoice-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .detail-icon {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            color: white;
        }

        .detail-content h6 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
        }

        .detail-content p {
            margin: 0;
            color: #7f8c8d;
        }

        .payment-summary {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }

        .amount-row:not(:last-child) {
            border-bottom: 1px solid #dee2e6;
        }

        .amount-row.total {
            font-size: 1.3em;
            font-weight: 700;
            color: #e74c3c;
            border-bottom: none;
            margin-bottom: 0;
        }

        .payment-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .payment-method {
            position: relative;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #00b894;
            transform: translateY(-2px);
        }

        .payment-method.selected {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border-color: #00b894;
            color: white;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-method i {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }

        .payment-method .method-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .payment-method .method-desc {
            font-size: 0.85em;
            opacity: 0.8;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: #00b894;
            box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.25);
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 12px 30px;
        }

        .btn-success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #00a085 0%, #008f7a 100%);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 20px;
                text-align: center;
            }

            .invoice-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .invoice-details {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-credit-card me-3"></i>Make Payment</h1>
            <p class="mb-0">Complete your payment for this invoice</p>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <div class="invoice-header">
                <div>
                    <div class="invoice-number">Invoice #<?php echo str_pad($invoice['invoice_id'], 6, '0', STR_PAD_LEFT); ?></div>
                    <div class="text-muted">Issued on <?php echo date('M j, Y', strtotime($invoice['invoice_date'])); ?></div>
                </div>
                <span class="status-badge">
                    <?php echo ucfirst($invoice['payment_status']); ?>
                </span>
            </div>

            <div class="invoice-details">
                <div class="detail-item">
                    <div class="detail-icon" style="background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <div class="detail-content">
                        <h6>Service</h6>
                        <p><?php echo htmlspecialchars($invoice['service_name'] ?? 'General Service'); ?></p>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-icon" style="background: linear-gradient(135deg, #e17055 0%, #d63031 100%);">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="detail-content">
                        <h6>Vehicle</h6>
                        <p><?php echo htmlspecialchars($invoice['plate_number'] ?? 'N/A'); ?></p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon" style="background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="detail-content">
                        <h6>Service Date</h6>
                        <p><?php echo $invoice['appointment_date'] ? date('M j, Y', strtotime($invoice['appointment_date'])) : 'TBD'; ?></p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-icon" style="background: linear-gradient(135deg, #00b894 0%, #00a085 100%);">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="detail-content">
                        <h6>Client</h6>
                        <p><?php echo htmlspecialchars($invoice['first_name'] . ' ' . $invoice['last_name']); ?></p>
                    </div>
                </div>
            </div>

            <div class="payment-summary">
                <div class="amount-row">
                    <span>Subtotal:</span>
                    <span>₱<?php echo number_format($invoice['subtotal'] ?? $invoice['grand_total'], 2); ?></span>
                </div>
                
                <?php if ($invoice['tax_amount'] && $invoice['tax_amount'] > 0): ?>
                <div class="amount-row">
                    <span>Tax:</span>
                    <span>₱<?php echo number_format($invoice['tax_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="amount-row">
                    <span>Total Amount:</span>
                    <span>₱<?php echo number_format($invoice['grand_total'], 2); ?></span>
                </div>
                
                <?php if ($total_paid > 0): ?>
                <div class="amount-row">
                    <span>Amount Paid:</span>
                    <span style="color: #00b894;">-₱<?php echo number_format($total_paid, 2); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="amount-row total">
                    <span>Amount Due:</span>
                    <span>₱<?php echo number_format($remaining_balance, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="payment-form">
            <form method="POST" id="paymentForm">
                <!-- Payment Method Selection -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Select Payment Method
                    </h4>
                    
                    <div class="payment-methods">
                        <label class="payment-method" for="credit_card">
                            <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                            <i class="fas fa-credit-card"></i>
                            <div class="method-name">Credit Card</div>
                            <div class="method-desc">Visa, MasterCard, etc.</div>
                        </label>

                        <label class="payment-method" for="gcash">
                            <input type="radio" name="payment_method" value="gcash" id="gcash" required>
                            <i class="fab fa-google-wallet" style="color: #007dff;"></i>
                            <div class="method-name">GCash</div>
                            <div class="method-desc">Mobile wallet payment</div>
                        </label>

                        <label class="payment-method" for="paymaya">
                            <input type="radio" name="payment_method" value="paymaya" id="paymaya" required>
                            <i class="fas fa-mobile-alt" style="color: #00d4ff;"></i>
                            <div class="method-name">PayMaya</div>
                            <div class="method-desc">Digital wallet</div>
                        </label>

                        <label class="payment-method" for="bank_transfer">
                            <input type="radio" name="payment_method" value="bank_transfer" id="bank_transfer" required>
                            <i class="fas fa-university"></i>
                            <div class="method-name">Bank Transfer</div>
                            <div class="method-desc">Online banking</div>
                        </label>

                        <label class="payment-method" for="cash">
                            <input type="radio" name="payment_method" value="cash" id="cash" required>
                            <i class="fas fa-money-bill-wave"></i>
                            <div class="method-name">Cash</div>
                            <div class="method-desc">Pay at location</div>
                        </label>
                    </div>
                </div>

                <!-- Payment Details -->
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-money-check-alt"></i>
                        Payment Details
                    </h4>

                    <div class="row">
                        <div class="col-md-6">
                            <label for="amount" class="form-label">Payment Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="1" 
                                       max="<?php echo $remaining_balance; ?>" 
                                       value="<?php echo $remaining_balance; ?>" 
                                       required>
                            </div>
                            <small class="text-muted">Maximum: ₱<?php echo number_format($remaining_balance, 2); ?></small>
                        </div>

                        <div class="col-md-6">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="reference_number" 
                                   name="reference_number"
                                   placeholder="Transaction/Reference number (optional)">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Additional notes about this payment (optional)"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-success flex-fill">
                        <i class="fas fa-credit-card me-2"></i>
                        Process Payment
                    </button>
                    
                    <a href="/vehicare_db/client/invoice-details.php?id=<?php echo $invoice['invoice_id']; ?>" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.payment-method').forEach(method => {
                    method.classList.remove('selected');
                });
                this.closest('.payment-method').classList.add('selected');
            });
        });

        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const amount = parseFloat(document.getElementById('amount').value);
            const maxAmount = <?php echo $remaining_balance; ?>;
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked');

            if (!selectedMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }

            if (amount <= 0 || amount > maxAmount) {
                e.preventDefault();
                alert('Please enter a valid payment amount between ₱1.00 and ₱' + maxAmount.toFixed(2) + '.');
                return;
            }

            // Additional validation based on payment method
            if (selectedMethod.value !== 'cash') {
                const confirmed = confirm('You will be redirected to the payment processor to complete your payment. Continue?');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>
</html>