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
        .code-box {
            background: #ffffff;
            border: 2px dashed #3b82f6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
            letter-spacing: 5px;
            font-family: 'Courier New', monospace;
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
            background: #3b82f6;
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
            <h2>Email Verification</h2>
        </div>
        
        <p>Hi <?= esc($name) ?>,</p>
        
        <p>Thank you for registering with ClearPay! To complete your registration, please verify your email address using the verification code below:</p>
        
        <div class="code-box">
            <div class="verification-code"><?= $code ?></div>
        </div>
        
        <div class="info">
            <strong>Important:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This code will expire in 24 hours</li>
                <li>Enter this code in the verification modal on our website</li>
                <li>If you didn't create an account, please ignore this email</li>
            </ul>
        </div>
        
        <p>If you have any questions, please contact our support team.</p>
        
        <div class="footer">
            <p>&copy; <?= date('Y') ?> ClearPay. All rights reserved.</p>
            <p>This is an automated message, please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
