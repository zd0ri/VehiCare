<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// Check if client is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: /vehicare_db/login.php");
    exit;
}

$client_id = $_SESSION['user_id'];
$payment_id = $_GET['payment'] ?? null;

if (!$payment_id) {
    header("Location: /vehicare_db/client/payments.php");
    exit;
}

// Get payment details
$payment_query = "
    SELECT p.*, i.invoice_id, i.invoice_date, i.grand_total as invoice_total,
           a.appointment_date, s.service_name, v.plate_number, v.car_brand, v.car_model,
           u.first_name, u.last_name, u.email
    FROM payments p
    LEFT JOIN invoices i ON p.invoice_id = i.invoice_id
    LEFT JOIN appointments a ON i.appointment_id = a.appointment_id
    LEFT JOIN services s ON a.service_id = s.service_id
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    LEFT JOIN users u ON p.client_id = u.user_id
    WHERE p.payment_id = ? AND p.client_id = ?
";

$stmt = $conn->prepare($payment_query);
$stmt->bind_param("ii", $payment_id, $client_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    header("Location: /vehicare_db/client/payments.php");
    exit;
}

include __DIR__ . '/../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - VehiCare</title>
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

        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: bounceIn 1s ease-out;
        }

        .success-icon i {
            font-size: 3em;
            color: white;
        }

        .success-title {
            font-size: 2.5em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .success-subtitle {
            font-size: 1.2em;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .payment-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: left;
        }

        .details-header {
            text-align: center;
            font-size: 1.3em;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row.highlight {
            background: white;
            padding: 20px;
            margin: 0 -15px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.2em;
            color: #00b894;
        }

        .detail-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-value {
            color: #7f8c8d;
            font-weight: 500;
        }

        .payment-id {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1em;
            display: inline-block;
            margin: 20px 0;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 12px 30px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            border: none;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-completed { 
            background: #d4edda; 
            color: #155724; 
        }

        .status-pending { 
            background: #fff3cd; 
            color: #856404; 
        }

        .next-steps {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .next-steps h5 {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            flex-shrink: 0;
        }

        .step-content h6 {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .step-content p {
            color: #7f8c8d;
            margin: 0;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .success-card {
                padding: 30px 20px;
            }

            .success-title {
                font-size: 2em;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Success Message -->
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-subtitle">Your payment has been processed successfully</p>
            
            <div class="payment-id">
                Payment #<?php echo str_pad($payment['payment_id'], 6, '0', STR_PAD_LEFT); ?>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="payment-details">
            <h4 class="details-header">Payment Summary</h4>
            
            <div class="detail-row">
                <span class="detail-label">Payment Date:</span>
                <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($payment['payment_date'])); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value"><?php echo str_replace('_', ' ', ucwords($payment['payment_method'])); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                    <?php echo ucfirst($payment['payment_status']); ?>
                </span>
            </div>

            <?php if ($payment['reference_number']): ?>
            <div class="detail-row">
                <span class="detail-label">Reference Number:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['reference_number']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($payment['invoice_id']): ?>
            <div class="detail-row">
                <span class="detail-label">Invoice:</span>
                <span class="detail-value">#<?php echo str_pad($payment['invoice_id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($payment['service_name']): ?>
            <div class="detail-row">
                <span class="detail-label">Service:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['service_name']); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($payment['plate_number']): ?>
            <div class="detail-row">
                <span class="detail-label">Vehicle:</span>
                <span class="detail-value"><?php echo htmlspecialchars($payment['plate_number']); ?></span>
            </div>
            <?php endif; ?>

            <div class="detail-row highlight">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">₱<?php echo number_format($payment['amount'], 2); ?></span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="/vehicare_db/client/payment-receipt.php?id=<?php echo $payment['payment_id']; ?>" 
               class="btn btn-success">
                <i class="fas fa-download me-2"></i>
                Download Receipt
            </a>
            
            <?php if ($payment['invoice_id']): ?>
            <a href="/vehicare_db/client/invoice-details.php?id=<?php echo $payment['invoice_id']; ?>" 
               class="btn btn-primary">
                <i class="fas fa-file-invoice me-2"></i>
                View Invoice
            </a>
            <?php endif; ?>
            
            <a href="/vehicare_db/client/payments.php" 
               class="btn btn-secondary">
                <i class="fas fa-history me-2"></i>
                Payment History
            </a>
        </div>

        <!-- Next Steps -->
        <div class="next-steps mt-4">
            <h5><i class="fas fa-lightbulb me-2"></i>What's Next?</h5>
            
            <div class="step-item">
                <div class="step-icon">1</div>
                <div class="step-content">
                    <h6>Receipt Confirmation</h6>
                    <p>A payment receipt has been generated. You can download it using the button above.</p>
                </div>
            </div>

            <?php if ($payment['payment_status'] === 'pending'): ?>
            <div class="step-item">
                <div class="step-icon">2</div>
                <div class="step-content">
                    <h6>Payment Verification</h6>
                    <p>Your cash payment will be verified when you arrive for your appointment.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="step-item">
                <div class="step-icon"><?php echo $payment['payment_status'] === 'pending' ? '3' : '2'; ?></div>
                <div class="step-content">
                    <h6>Service Confirmation</h6>
                    <p>We'll send you a confirmation and reminder before your scheduled service appointment.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-icon"><?php echo $payment['payment_status'] === 'pending' ? '4' : '3'; ?></div>
                <div class="step-content">
                    <h6>Need Help?</h6>
                    <p>If you have any questions about your payment or service, feel free to contact our support team.</p>
                </div>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mt-4">
            <a href="/vehicare_db/client/dashboard.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-redirect after 30 seconds
        setTimeout(() => {
            const redirect = confirm('Would you like to return to your dashboard?');
            if (redirect) {
                window.location.href = '/vehicare_db/client/dashboard.php';
            }
        }, 30000);
    </script>
</body>
</html>