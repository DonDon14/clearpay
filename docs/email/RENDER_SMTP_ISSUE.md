# Render.com SMTP Email Issue - Comprehensive Troubleshooting

## ⚠️ Critical Issue: Render.com May Block SMTP Ports

**Render.com's free tier may block outbound SMTP connections** on ports 587 and 465. This is a common restriction on free hosting platforms.

## Possible Root Causes

1. **Render blocks SMTP ports** (most likely on free tier)
2. **Gmail App Password is incorrect or expired**
3. **Network/firewall restrictions**
4. **OpenSSL/TLS configuration issues**

## Step 1: Check Render Logs for Actual Error

1. Go to **Render Dashboard** → **Your service** → **Logs**
2. Look for lines containing:
   - `SMTP`
   - `email`
   - `connection`
   - `authentication`
   - `timeout`
   - `blocked`
3. Copy the **exact error message**

## Step 2: Verify Gmail App Password

1. Go to **Google Account** → **Security** → **2-Step Verification** → **App Passwords**
2. **Delete the old App Password** (if it exists)
3. **Generate a NEW App Password**:
   - Select "Mail" as the app
   - Select "Other" as device
   - Name it "ClearPay Render"
   - Copy the 16-character password (e.g., `abcd efgh ijkl mnop`)
4. **Remove spaces**: `abcdefghijklmnop`
5. **Update in Render dashboard**:
   - Go to **Environment** tab
   - Update `email.SMTPPass` with the new password (no spaces)
   - Save and wait for redeploy

## Step 3: Try Alternative SMTP Configuration

### Option A: Use Port 465 with SSL

If port 587 is blocked, try port 465:

1. In **SMTP Configuration modal**:
   - **Port**: `465`
   - **Encryption**: `SSL` (not TLS)
2. Save and test

### Option B: Use Resend (Recommended - Easy Approval)

**Resend** is modern, developer-friendly, and has easy approval:

1. **Sign up**: https://resend.com
   - Free tier: 3,000 emails/month
   - Very easy approval (usually instant)
   - No credit card required
2. **Get API Key**:
   - Go to **API Keys** → **Create API Key**
   - Copy the API key (starts with `re_`)
3. **Update SMTP settings**:
   - **SMTP Host**: `smtp.resend.com`
   - **SMTP User**: `resend`
   - **SMTP Password**: `[Your Resend API Key]`
   - **Port**: `587`
   - **Encryption**: `TLS`

### Option C: Use Mailgun (Alternative)

**Mailgun** is reliable and easy to set up:

1. **Sign up**: https://www.mailgun.com
   - Free tier: 5,000 emails/month (first 3 months)
   - Easy approval process
2. **Get SMTP credentials**:
   - Go to **Sending** → **Domain Settings** → **SMTP credentials**
   - Or use **API Keys** section
3. **Update SMTP settings**:
   - **SMTP Host**: `smtp.mailgun.org`
   - **SMTP User**: `postmaster@[your-domain].mailgun.org`
   - **SMTP Password**: `[Your Mailgun SMTP Password]`
   - **Port**: `587`
   - **Encryption**: `TLS`

## Step 4: Check if Render Blocks SMTP

### Test Connection from Render

Create a test endpoint to check if SMTP ports are accessible:

```php
// In a test controller
public function testSmtpConnection()
{
    $host = 'smtp.gmail.com';
    $port = 587;
    
    $connection = @fsockopen($host, $port, $errno, $errstr, 10);
    
    if ($connection) {
        fclose($connection);
        return "✅ Port $port is accessible";
    } else {
        return "❌ Port $port is BLOCKED: $errstr ($errno)";
    }
}
```

## Step 5: Alternative Solutions

### Solution 1: Use Render's Email Service (if available)

Check if Render offers an email service or addon.

### Solution 2: Use AWS SES

1. Sign up for AWS SES
2. Verify your email domain
3. Get SMTP credentials
4. Use AWS SES SMTP endpoint

### Solution 3: Use a Webhook/API Service

Instead of SMTP, use an API-based email service:
- **SendGrid API** (recommended)
- **Mailgun API**
- **Postmark API**
- **Resend API**

## Step 6: Verify Current Error

After the latest code update, the error message should be more specific. Check:

1. **Render logs** for the exact error
2. **Browser console** for the error response
3. Look for keywords:
   - "Connection timeout" → Port blocked
   - "Authentication failed" → Wrong password
   - "SSL/TLS error" → Encryption issue

## Common Render-Specific Issues

1. **Free tier restrictions**: Free tier may block SMTP ports
2. **Network policies**: Render may restrict outbound connections
3. **Firewall rules**: Automatic firewall may block SMTP

## Recommended Solution

**Use SendGrid or Mailgun** instead of Gmail SMTP:
- ✅ Works reliably on Render
- ✅ Free tier available
- ✅ Better deliverability
- ✅ No port blocking issues

## Next Steps

1. **Check Render logs** for the exact error message
2. **Try generating a new Gmail App Password**
3. **If still failing, switch to SendGrid/Mailgun**
4. **Share the exact error message** from logs for further debugging

---

**Note**: The latest code update includes better error logging. Check Render logs after the next deployment to see the specific error.

