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
        .success-box {
            background: #d1fae5;
            border: 2px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .success-icon {
            font-size: 48px;
            color: #10b981;
            margin-bottom: 15px;
        }
        .info {
            background: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #3b82f6;
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
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #10b981;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">ClearPay</div>
            <h2>Account Approved</h2>
        </div>
        
        <p>Hi <?= esc($name) ?>,</p>
        
        <div class="success-box">
            <div class="success-icon">✓</div>
            <h3 style="color: #10b981; margin: 0;">Your Officer Account Has Been Approved!</h3>
        </div>
        
        <p>Great news! Your officer account registration has been reviewed and approved by the Super Admin.</p>
        
        <div class="info">
            <strong>What's Next?</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>You can now log in to the ClearPay Admin Portal</li>
                <li>Use your registered username: <strong><?= esc($username) ?></strong></li>
                <li>Access the portal at: <a href="<?= base_url() ?>"><?= base_url() ?></a></li>
            </ul>
        </div>
        
        <p>If you have any questions or need assistance, please contact the system administrator.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="<?= base_url() ?>" class="button">Login to Portal</a>
        </div>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>

