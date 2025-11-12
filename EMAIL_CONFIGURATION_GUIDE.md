# Email Configuration Guide for Render Deployment

## Overview

The email system has been updated to use environment variables, making it easy to configure for Render deployment without modifying code.

## Email Functionalities

The application uses email for:

1. **Email Verification** - Sent during user/payer registration
2. **Password Reset** - Sent when users request password reset

## Configuration for Render

### Step 1: Set Environment Variables in Render Dashboard

1. Go to your Render dashboard
2. Select your service (`clearpay-web-dev`)
3. Go to **Environment** tab
4. Add the following environment variables:

```
email.fromEmail = project.clearpay@gmail.com
email.fromName = ClearPay
email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPUser = project.clearpay@gmail.com
email.SMTPPass = YOUR_GMAIL_APP_PASSWORD_HERE
email.SMTPPort = 587
email.SMTPCrypto = tls
email.mailType = html
```

### Step 2: Get Gmail App Password

1. Go to your Google Account settings
2. Navigate to **Security** → **2-Step Verification**
3. Enable 2-Step Verification if not already enabled
4. Go to **App passwords** section
5. Create a new app password for "Mail"
6. Copy the 16-character password
7. Paste it into `email.SMTPPass` in Render dashboard

### Step 3: Verify Configuration

After setting environment variables, restart your Render service. The email configuration will be loaded automatically.

## Testing Email

1. **Test Email Verification:**
   - Register a new payer/user
   - Check if verification email is received
   - Check Render logs for email sending status

2. **Test Password Reset:**
   - Go to forgot password page
   - Enter email address
   - Check if reset code email is received

## Troubleshooting

### Emails Not Sending

1. **Check Render Logs:**
   - Go to Render dashboard → Logs
   - Look for email-related errors
   - Common errors:
     - "SMTP authentication failed" → Check `email.SMTPPass`
     - "Connection timeout" → Check `email.SMTPHost` and `email.SMTPPort`
     - "Invalid credentials" → Verify Gmail app password

2. **Verify Environment Variables:**
   - Ensure all email variables are set in Render dashboard
   - Check for typos in variable names
   - Verify Gmail app password is correct

3. **Check Gmail Settings:**
   - Ensure 2-Step Verification is enabled
   - Verify app password is active
   - Check if "Less secure app access" is needed (usually not with app passwords)

### Common Issues

**Issue:** "SMTP authentication failed"
- **Solution:** Verify Gmail app password is correct and active

**Issue:** "Connection timeout"
- **Solution:** Check firewall settings and ensure port 587 is open

**Issue:** "Invalid email address"
- **Solution:** Verify `email.fromEmail` and `email.SMTPUser` match your Gmail address

## Email Templates

Email templates are located in:
- `app/Views/emails/verification.php` - Email verification template
- `app/Views/emails/password_reset.php` - Password reset template

These templates can be customized as needed.

## Security Notes

- **Never commit email passwords to Git**
- Use environment variables for all sensitive email configuration
- Gmail app passwords are safer than regular passwords
- Consider using a dedicated email service (SendGrid, Mailgun) for production

## Code Changes

The email configuration now loads from environment variables in `app/Config/Email.php`:

```php
public function __construct()
{
    parent::__construct();
    
    // Load from environment variables with fallback to defaults
    $this->fromEmail = $_ENV['email.fromEmail'] ?? getenv('email.fromEmail') ?: 'project.clearpay@gmail.com';
    // ... other settings
}
```

This allows easy configuration without code changes.

