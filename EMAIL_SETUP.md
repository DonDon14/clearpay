# Email Setup Guide for ClearPay

This guide will help you configure real email verification for the registration system.

## Overview

The email verification system sends a 6-digit code to users' email addresses when they register. The code must be entered to verify their email address and complete registration.

## Configuration Steps

### For Gmail Users

1. **Enable 2-Step Verification**
   - Go to your Google Account settings
   - Navigate to: **Security** → **2-Step Verification**
   - Enable 2-Step Verification if not already enabled

2. **Generate an App Password**
   - Still in **Security** settings
   - Find **App passwords** section
   - Create a new app password
   - Select "Mail" and your device
   - Copy the generated 16-character password

3. **Configure Email Settings**
   - Open `app/Config/Email.php`
   - Update the following values:

```php
public string $fromEmail  = 'your-email@gmail.com'; // Your Gmail address
public string $fromName   = 'ClearPay';
public string $SMTPHost = 'smtp.gmail.com';
public string $SMTPUser = 'your-email@gmail.com'; // Your Gmail address
public string $SMTPPass = 'your-16-char-app-password'; // App password from step 2
public int $SMTPPort = 587;
public string $SMTPCrypto = 'tls';
public string $mailType = 'html';
```

### For Other Email Providers

#### Outlook/Hotmail
```php
public string $SMTPHost = 'smtp-mail.outlook.com';
public string $SMTPUser = 'your-email@outlook.com';
public string $SMTPPass = 'your-password';
public int $SMTPPort = 587;
public string $SMTPCrypto = 'tls';
```

#### Yahoo Mail
```php
public string $SMTPHost = 'smtp.mail.yahoo.com';
public string $SMTPUser = 'your-email@yahoo.com';
public string $SMTPPass = 'your-app-password'; // Use App Password
public int $SMTPPort = 587;
public string $SMTPCrypto = 'tls';
```

#### Custom SMTP (SendGrid, Mailgun, etc.)
```php
public string $SMTPHost = 'smtp.sendgrid.net'; // Your SMTP host
public string $SMTPUser = 'your-username';
public string $SMTPPass = 'your-api-key';
public int $SMTPPort = 587;
public string $SMTPCrypto = 'tls';
```

## Testing Email Configuration

1. Register a new account on your application
2. Check if the verification email is received
3. Check the logs at `writable/logs/log-YYYY-MM-DD.log` for email status

## Troubleshooting

### Email not being sent?

1. **Check your SMTP credentials**
   - Verify username and password are correct
   - For Gmail, ensure you're using App Password, not regular password

2. **Check port and encryption settings**
   - Port 587 uses TLS encryption
   - Port 465 uses SSL encryption (set `SMTPCrypto = 'ssl'`)
   - Port 25 is usually blocked by ISPs

3. **Check firewall settings**
   - Ensure your server can make outbound connections on SMTP ports
   - Port 587 (TLS) or 465 (SSL)

4. **Check email logs**
   ```bash
   tail -f writable/logs/log-$(date +%Y-%m-%d).log
   ```

5. **Test SMTP connection**
   Create a test file `test_email.php`:
   ```php
   <?php
   require 'vendor/autoload.php';
   
   $email = \Config\Services::email();
   $email->setFrom('your-email@gmail.com', 'Test');
   $email->setTo('recipient@example.com');
   $email->setSubject('Test Email');
   $email->setMessage('This is a test email.');
   
   if ($email->send()) {
       echo "Email sent successfully!";
   } else {
       echo "Failed to send email: " . $email->printDebugger();
   }
   ```

### Common Errors

**"Failed to connect to mail server"**
- Check SMTP host and port
- Verify firewall allows outbound SMTP connections

**"Authentication failed"**
- Check username and password
- For Gmail, ensure App Password is used
- Check if 2-Step Verification is enabled

**"Connection timed out"**
- Check if SMTP port is open
- Try different port (587, 465, 25)
- Check firewall settings

## Development Mode

For local development without real email, you can:

1. Check the console log after registration - the verification code is displayed in the response
2. Check the browser's developer tools Network tab
3. Check the application logs

The verification code is also stored in the database in the `users` table's `verification_token` column.

## Security Notes

- Never commit your email credentials to version control
- Use environment variables for sensitive data in production
- Consider using a dedicated email service (SendGrid, Mailgun) for production
- Rate limit email sending to prevent abuse

## Production Recommendations

For production environments, consider:

1. **Email Service Providers**
   - SendGrid: Free tier (100 emails/day)
   - Mailgun: Free tier (5,000 emails/month)
   - Amazon SES: Pay-as-you-go
   - Postmark: Reliable transactional emails

2. **Environment Configuration**
   - Move email credentials to environment variables
   - Use different credentials for development and production

3. **Rate Limiting**
   - Implement rate limiting on registration endpoint
   - Limit number of verification emails per hour

4. **Email Templates**
   - Customize email templates to match your brand
   - Add your logo and branding colors

## Support

If you continue to experience issues:
1. Check the application logs: `writable/logs/`
2. Enable debugging in CodeIgniter
3. Test SMTP connection with a simple script
4. Contact your hosting provider regarding SMTP restrictions
