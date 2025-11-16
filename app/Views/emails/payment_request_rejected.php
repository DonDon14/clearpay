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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 8px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .request-box {
            background: #ffffff;
            border: 2px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #dc3545;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
        }
        .label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .info-section {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
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
        }
        .admin-notes {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .admin-notes-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ClearPay</div>
            <div style="font-size: 18px; margin-top: 10px;">Payment Request Rejected</div>
        </div>

        <p>Dear <?= esc($payerName) ?>,</p>

        <p>We regret to inform you that your payment request has been rejected.</p>

        <div class="request-box">
            <div class="label">Requested Amount</div>
            <div class="amount">₱<?= number_format($requestedAmount, 2) ?></div>
        </div>

        <div class="info-section">
            <div class="info-row">
                <span class="info-label">Reference Number:</span>
                <span class="info-value"><?= esc($referenceNumber ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Contribution:</span>
                <span class="info-value"><?= esc($contributionTitle ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment Method:</span>
                <span class="info-value"><?= esc($paymentMethod ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Request Date:</span>
                <span class="info-value"><?= esc($requestDate ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Rejected Date:</span>
                <span class="info-value"><?= esc($rejectedDate ?? 'N/A') ?></span>
            </div>
        </div>

        <?php if (!empty($adminNotes)): ?>
        <div class="admin-notes">
            <div class="admin-notes-title">Reason for Rejection:</div>
            <div><?= nl2br(esc($adminNotes)) ?></div>
        </div>
        <?php endif; ?>

        <p>If you have any questions or concerns about this rejection, please contact our support team.</p>

        <p>Thank you for your understanding.</p>

        <div class="footer">
            <p>This is an automated email from ClearPay. Please do not reply to this email.</p>
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

