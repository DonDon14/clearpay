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
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 10px;
        }
        .rejection-box {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .rejection-icon {
            font-size: 48px;
            color: #ef4444;
            margin-bottom: 15px;
        }
        .info {
            background: #fef3c7;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
        .reason-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 15px;
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
            <h2>Account Registration Update</h2>
        </div>
        
        <p>Hi <?= esc($name) ?>,</p>
        
        <div class="rejection-box">
            <div class="rejection-icon">✗</div>
            <h3 style="color: #ef4444; margin: 0;">Your Officer Account Registration Has Been Rejected</h3>
        </div>
        
        <p>We regret to inform you that your officer account registration has been reviewed and unfortunately, it has not been approved at this time.</p>
        
        <?php if (!empty($reason) && $reason !== 'No reason provided'): ?>
        <div class="reason-box">
            <strong>Reason:</strong>
            <p style="margin: 10px 0 0 0;"><?= esc($reason) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>What This Means:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>You will not be able to log in to the ClearPay Admin Portal</li>
                <li>If you believe this is an error, please contact the system administrator</li>
                <li>You may be able to reapply in the future if circumstances change</li>
            </ul>
        </div>
        
        <p>If you have any questions or would like to discuss this decision, please contact the system administrator for assistance.</p>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

