# Email Service Alternatives for ClearPay

Since SendGrid declined your account, here are **easier alternatives** that work well with Render:

## 🎯 Recommended: Resend (Easiest Setup)

**Resend** is modern, developer-friendly, and has a generous free tier.

### Setup Steps:
1. **Sign up**: https://resend.com
   - Very easy approval process
   - Free tier: 3,000 emails/month
   - No credit card required initially

2. **Get SMTP credentials**:
   - Go to **API Keys** → **Create API Key**
   - Or use **SMTP** tab for SMTP credentials

3. **SMTP Settings**:
   - **SMTP Host**: `smtp.resend.com`
   - **SMTP User**: `resend`
   - **SMTP Password**: `[Your Resend API Key]`
   - **Port**: `587`
   - **Encryption**: `TLS`

### Why Resend?
- ✅ Easy approval (usually instant)
- ✅ Great free tier
- ✅ Works perfectly on Render
- ✅ Modern API and SMTP
- ✅ Good deliverability

---

## Option 2: Mailgun (Reliable)

**Mailgun** is another excellent option with easy signup.

### Setup Steps:
1. **Sign up**: https://www.mailgun.com
   - Free tier: 5,000 emails/month for 3 months, then 1,000/month
   - Easy approval process

2. **Get SMTP credentials**:
   - Go to **Sending** → **Domain Settings** → **SMTP credentials**
   - Or use **API Keys** section

3. **SMTP Settings**:
   - **SMTP Host**: `smtp.mailgun.org`
   - **SMTP User**: `postmaster@[your-domain].mailgun.org`
   - **SMTP Password**: `[Your Mailgun SMTP Password]`
   - **Port**: `587`
   - **Encryption**: `TLS`

---

## Option 3: Brevo (formerly Sendinblue)

**Brevo** offers a generous free tier and easy setup.

### Setup Steps:
1. **Sign up**: https://www.brevo.com
   - Free tier: 300 emails/day
   - Very easy approval

2. **Get SMTP credentials**:
   - Go to **SMTP & API** → **SMTP**
   - Copy SMTP server, login, and password

3. **SMTP Settings**:
   - **SMTP Host**: `smtp-relay.brevo.com`
   - **SMTP User**: `[Your Brevo SMTP Login]`
   - **SMTP Password**: `[Your Brevo SMTP Password]`
   - **Port**: `587`
   - **Encryption**: `TLS`

---

## Option 4: AWS SES (If you have AWS account)

**AWS SES** is very reliable but requires AWS account setup.

### Setup Steps:
1. **Sign up for AWS**: https://aws.amazon.com
2. **Go to SES**: Amazon SES console
3. **Verify email**: Verify your sender email
4. **Get SMTP credentials**: Create SMTP credentials

### SMTP Settings:
- **SMTP Host**: `email-smtp.[region].amazonaws.com` (e.g., `email-smtp.us-east-1.amazonaws.com`)
- **SMTP User**: `[Your AWS SMTP Username]`
- **SMTP Password**: `[Your AWS SMTP Password]`
- **Port**: `587`
- **Encryption**: `TLS`

**Note**: AWS SES requires email verification and may have sandbox mode restrictions initially.

---

## Option 5: Postmark (Premium but reliable)

**Postmark** is premium but very reliable.

### Setup Steps:
1. **Sign up**: https://postmarkapp.com
   - Free tier: 100 emails/month
   - Easy approval

2. **Get SMTP credentials**:
   - Go to **Servers** → **Your Server** → **SMTP**
   - Copy SMTP credentials

### SMTP Settings:
- **SMTP Host**: `smtp.postmarkapp.com`
- **SMTP User**: `[Your Postmark Server API Token]`
- **SMTP Password**: `[Your Postmark Server API Token]` (same as username)
- **Port**: `587`
- **Encryption**: `TLS`

---

## 🚀 Quick Setup Guide (Using Resend - Recommended)

### Step 1: Sign Up
1. Go to https://resend.com
2. Click "Sign Up"
3. Use your email (can be Gmail)
4. Verify email
5. **No credit card required for free tier**

### Step 2: Get API Key
1. After login, go to **API Keys**
2. Click **Create API Key**
3. Name it "ClearPay Production"
4. Copy the API key (starts with `re_`)

### Step 3: Configure in ClearPay
1. Go to **Settings** → **SMTP Configuration**
2. Enter:
   - **SMTP Host**: `smtp.resend.com`
   - **SMTP User**: `resend`
   - **SMTP Password**: `[Your Resend API Key]` (paste the key you copied)
   - **Port**: `587`
   - **Encryption**: `TLS`
   - **From Email**: `project.clearpay@gmail.com` (or verify domain in Resend)
   - **From Name**: `ClearPay`
3. Click **Save Configuration**
4. Click **Send Test**

### Step 4: Verify Domain (Optional but Recommended)
1. In Resend dashboard, go to **Domains**
2. Add your domain (if you have one)
3. Add DNS records to verify
4. This improves deliverability

---

## 📊 Comparison Table

| Service | Free Tier | Approval | Best For |
|---------|-----------|----------|----------|
| **Resend** | 3,000/month | ⭐⭐⭐⭐⭐ Easy | **Recommended** |
| **Mailgun** | 5,000/month (3mo) | ⭐⭐⭐⭐ Easy | High volume |
| **Brevo** | 300/day | ⭐⭐⭐⭐⭐ Very Easy | Small to medium |
| **AWS SES** | 62,000/month | ⭐⭐⭐ Moderate | Enterprise |
| **Postmark** | 100/month | ⭐⭐⭐⭐ Easy | Premium needs |

---

## ⚠️ Important Notes

1. **Gmail SMTP on Render**: May not work due to port blocking
2. **Free tiers**: Usually sufficient for development and small projects
3. **Domain verification**: Improves deliverability but not required initially
4. **API vs SMTP**: All services support SMTP (what we're using)

---

## 🔧 If You Still Want to Use Gmail

If you really want to use Gmail, try:

1. **Generate a NEW App Password**:
   - Delete old one
   - Create new App Password
   - Make sure 2-Step Verification is enabled

2. **Try Port 465 with SSL**:
   - Port: `465`
   - Encryption: `SSL` (not TLS)

3. **Check Render logs** for the exact error after deploying latest code

4. **Contact Render support** to ask if SMTP ports are blocked on free tier

---

## ✅ Recommended Next Steps

1. **Sign up for Resend** (easiest)
2. **Get API key**
3. **Configure in ClearPay admin panel**
4. **Test email sending**

This should work immediately on Render! 🎉

