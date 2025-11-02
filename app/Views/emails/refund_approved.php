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
            background: linear-gradient(135deg, #28a745 0%, #20883e 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 8px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .refund-box {
            background: #ffffff;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .refund-amount {
            font-size: 28px;
            font-weight: bold;
            color: #28a745;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
        }
        .refund-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .info-section {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #28a745;
        }
        .info-section h3 {
            margin: 0 0 15px 0;
            color: #28a745;
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
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #28a745;
            color: white;
        }
        .note-box {
            background: #e7f3ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
            <h2 style="margin: 0; font-size: 20px;">Refund Approved</h2>
        </div>
        
        <p>Hi <?= esc($payerName) ?>,</p>
        
        <p>We are pleased to inform you that your refund request has been <strong>approved and completed</strong>. Your refund has been processed successfully.</p>
        
        <div class="refund-box">
            <div class="refund-label">Refund Amount</div>
            <div class="refund-amount">₱<?= number_format((float)$refundAmount, 2) ?></div>
        </div>

        <div class="info-section">
            <h3>📋 Refund Information</h3>
            <div class="info-row">
                <span class="info-label">Refund ID:</span>
                <span class="info-value">#<?= esc($refundId) ?></span>
            </div>
            <?php if (!empty($refundReference)): ?>
            <div class="info-row">
                <span class="info-label">Refund Reference:</span>
                <span class="info-value"><?= esc($refundReference) ?></span>
            </div>
            <?php endif; ?>
            <div class="info-row">
                <span class="info-label">Refund Method:</span>
                <span class="info-value"><?= esc($refundMethod) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">
                    <span class="status-badge">Approved & Completed</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Processed Date:</span>
                <span class="info-value"><?= esc($processedDate) ?></span>
            </div>
        </div>

        <div class="info-section">
            <h3>💳 Original Payment Details</h3>
            <div class="info-row">
                <span class="info-label">Receipt Number:</span>
                <span class="info-value"><?= esc($receiptNumber) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Date:</span>
                <span class="info-value"><?= esc($paymentDate) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Contribution:</span>
                <span class="info-value"><?= esc($contributionTitle) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Original Amount Paid:</span>
                <span class="info-value">₱<?= number_format((float)$amountPaid, 2) ?></span>
            </div>
        </div>

        <?php if (!empty($adminNotes)): ?>
        <div class="note-box">
            <h3 style="margin: 0 0 10px 0; color: #0d6efd; font-size: 14px;">📝 Admin Notes</h3>
            <p style="margin: 0; color: #333;"><?= esc($adminNotes) ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($refundReason)): ?>
        <div class="note-box">
            <h3 style="margin: 0 0 10px 0; color: #0d6efd; font-size: 14px;">💬 Refund Reason</h3>
            <p style="margin: 0; color: #333;"><?= esc($refundReason) ?></p>
        </div>
        <?php endif; ?>

        <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 5px; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; color: #0369a1;"><strong>Note:</strong> Your refund will be processed according to the refund method you selected. Please allow 3-5 business days for the refund to reflect in your account.</p>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
            <p>This is an automated notification email. Please do not reply to this email.</p>
            <p style="font-size: 12px; margin-top: 10px;">If you have any questions about this refund, please contact our support team.</p>
        </div>
    </div>
</body>
</html>

