# ✅ Resend Email Setup - Configuration Guide

## Your Resend API Key

**API Key**: `re_N7RDfQjN_8cDMqnNj4HK7uF9aP9WTZuxv`

⚠️ **IMPORTANT**: This key is saved locally in `resend-api-key.txt` (not in git) for your reference.

---

## 🚀 Quick Setup Instructions

### Option 1: Configure via Render Dashboard (Recommended)

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Navigate to your service**: `clearpay-web-dev`
3. **Go to Environment tab**
4. **Update these environment variables**:

   ```
   email.SMTPHost = smtp.resend.com
   email.SMTPUser = resend
   email.SMTPPass = re_N7RDfQjN_8cDMqnNj4HK7uF9aP9WTZuxv
   email.SMTPPort = 587
   email.SMTPCrypto = tls
   email.fromEmail = project.clearpay@gmail.com
   email.fromName = ClearPay
   ```

5. **Save changes** (Render will auto-redeploy)
6. **Wait for deployment** (2-5 minutes)
7. **Test email** from Settings page

### Option 2: Configure via Admin Panel (After Deployment)

1. **Login to your Render deployment**
2. **Go to Settings** → **SMTP Configuration**
3. **Enter the following**:
   - **From Email**: `project.clearpay@gmail.com`
   - **From Name**: `ClearPay`
   - **Protocol**: `SMTP`
   - **SMTP Host**: `smtp.resend.com`
   - **Port**: `587`
   - **SMTP Username**: `resend`
   - **SMTP Password**: `re_N7RDfQjN_8cDMqnNj4HK7uF9aP9WTZuxv` (use toggle to see it)
   - **Encryption**: `TLS`
   - **Timeout**: `30`
   - **Mail Type**: `HTML`
   - **Charset**: `UTF-8`
4. **Click "Save Configuration"**
5. **Click "Send Test"** to verify

---

## ✅ Resend SMTP Configuration Summary

| Setting | Value |
|---------|-------|
| **SMTP Host** | `smtp.resend.com` |
| **SMTP User** | `resend` |
| **SMTP Password** | `re_N7RDfQjN_8cDMqnNj4HK7uF9aP9WTZuxv` |
| **Port** | `587` |
| **Encryption** | `TLS` |
| **From Email** | `project.clearpay@gmail.com` |
| **From Name** | `ClearPay` |

---

## 🔒 Security Notes

1. ✅ **API key is saved locally** in `resend-api-key.txt` (not in git)
2. ✅ **File is in .gitignore** - won't be committed
3. ⚠️ **Don't share this key** publicly
4. ⚠️ **If key is compromised**, regenerate it in Resend dashboard

---

## 📧 Resend Free Tier Limits

- **3,000 emails/month** (free tier)
- **No credit card required**
- **Works perfectly on Render**
- **No port blocking issues**

---

## 🧪 Testing

After configuration:

1. **Go to Settings** → **SMTP Configuration**
2. **Click "Send Test"**
3. **Check your email inbox** (the email you configured)
4. **You should receive the test email!**

---

## 🐛 If Email Still Fails

1. **Check Render logs** for specific errors
2. **Verify API key** is correct (no extra spaces)
3. **Check Resend dashboard** for any account issues
4. **Verify "From Email"** is correct

---

## 📝 Next Steps

1. ✅ API key saved locally
2. ⏳ **Configure in Render dashboard** (Option 1) or **Admin panel** (Option 2)
3. ⏳ **Wait for deployment** (if using Render dashboard)
4. ⏳ **Test email sending**

**You're all set!** Once configured, emails should work perfectly on Render! 🎉

