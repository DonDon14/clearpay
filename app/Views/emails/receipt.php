<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #f9fafb;
            padding: 30px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 8px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .receipt-box {
            background: #ffffff;
            border: 2px solid #0d6efd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .receipt-number {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
        }
        .receipt-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-section {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #0d6efd;
        }
        .info-section h3 {
            margin: 0 0 15px 0;
            color: #0d6efd;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .amount-highlight {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        .badge-partial {
            background: #fd7e14;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ClearPay</div>
            <h2 style="margin: 0; font-size: 20px;">Payment Receipt</h2>
        </div>
        
        <p>Hi <?= esc($payerName) ?>,</p>
        
        <p>Thank you for your payment! This email confirms your payment has been successfully processed. Please save this receipt for your records.</p>
        
        <div class="receipt-box">
            <div class="receipt-label">Receipt Number</div>
            <div class="receipt-number"><?= esc($receiptNumber) ?></div>
        </div>

        <div class="info-section">
            <h3>📋 Receipt Information</h3>
            <div class="info-row">
                <span class="info-label">Receipt Number:</span>
                <span class="info-value"><?= esc($receiptNumber) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Date:</span>
                <span class="info-value"><?= esc($paymentDate) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Reference Number:</span>
                <span class="info-value"><?= esc($referenceNumber) ?></span>
            </div>
        </div>

        <div class="info-section">
            <h3>👤 Payer Information</h3>
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?= esc($payerName) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">ID Number:</span>
                <span class="info-value"><?= esc($payerId) ?></span>
            </div>
            <?php if (!empty($contactNumber)): ?>
            <div class="info-row">
                <span class="info-label">Contact Number:</span>
                <span class="info-value"><?= esc($contactNumber) ?></span>
            </div>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h3>💳 Payment Details</h3>
            <div class="info-row">
                <span class="info-label">Contribution Type:</span>
                <span class="info-value"><?= esc($contributionTitle) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?= esc($paymentMethod) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Amount Paid:</span>
                <span class="info-value amount-highlight">₱<?= number_format((float)$amountPaid, 2) ?></span>
            </div>
            <?php if (!empty($remainingBalance)): ?>
            <div class="info-row">
                <span class="info-label">Remaining Balance:</span>
                <span class="info-value">₱<?= number_format((float)$remainingBalance, 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row" style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #e5e7eb;">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge <?= esc($statusBadgeClass) ?>"><?= esc($statusText) ?></span>
                </span>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
            <p>This is an automated receipt email. Please do not reply to this email.</p>
            <p style="font-size: 12px; margin-top: 10px;">If you have any questions about this receipt, please contact our support team.</p>
        </div>
    </div>
</body>
</html>

