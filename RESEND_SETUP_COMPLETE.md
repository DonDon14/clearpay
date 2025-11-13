# ⚠️ Resend Email Setup - Domain Verification Required

## ⚠️ CRITICAL: Resend Cannot Send from Gmail Addresses

**Resend requires domain verification**, which means you **cannot send from `@gmail.com` addresses** because you don't own the Gmail domain.

**Error you're seeing**: "Invalid domain - You can only send from a verified domain"

---

## ✅ SOLUTION: Use Brevo Instead (Recommended)

**Brevo** (formerly Sendinblue) **allows Gmail addresses** without domain verification!

### Why Switch to Brevo:
- ✅ **Works with Gmail addresses** (`project.clearpay@gmail.com`)
- ✅ **No domain verification required**
- ✅ **Free tier**: 300 emails/day
- ✅ **Easy approval** (usually instant)
- ✅ **Works perfectly on Render**

**👉 See `BREVO_SETUP.md` for complete setup instructions**

---

## 📝 Your Resend API Key (Saved for Reference)

**API Key**: `re_N7RDfQjN_8cDMqnNj4HK7uF9aP9WTZuxv`

⚠️ **Note**: This key is saved locally in `resend-api-key.txt` (not in git) for your reference, but **Resend won't work with Gmail addresses**.

---

## ⚠️ IMPORTANT: Resend Domain Verification Requirement

**Before sending emails, you MUST verify `project.clearpay@gmail.com` in Resend:**

### Method 1: Via Broadcasts Tab (Recommended)

1. **Login to Resend**: https://resend.com (using `floroocero18@gmail.com` - that's fine!)
2. **Go to "Broadcasts"** section (in the left sidebar)
3. **Click "Create email"** or **"Add Email"** button
4. **When creating a broadcast**, you'll be prompted to verify the "From Email"
5. **Enter**: `project.clearpay@gmail.com`
6. **Check your Gmail inbox** for `project.clearpay@gmail.com`
7. **Click the verification link** in the email from Resend
8. **Wait for verification** (usually instant)

### Method 2: Via Domains Tab (Alternative)

1. **Go to "Domains"** section
2. **Click "Add domain"** (if you have your own domain)
3. **Or verify individual email** through the domain settings

### Method 3: Automatic Verification (When Sending)

Resend may automatically prompt you to verify the email when you first try to send from it. Just check the inbox and click the verification link.

**Note**: 
- ✅ Your Resend login email (`floroocero18@gmail.com`) can be different from your "From Email"
- ✅ You just need to verify the "From Email" (`project.clearpay@gmail.com`) in Resend
- ⚠️ **You cannot send emails until the "From Email" is verified**
- 💡 **Tip**: You can also verify by attempting to send a test email - Resend will send a verification email automatically

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

1. **Verify "From Email" in Resend first** (see above)
2. **Go to Settings** → **SMTP Configuration**
3. **Click "Send Test"**
4. **Check your email inbox** (the email you configured)
5. **You should receive the test email!**

**If email fails with "unverified sender" error:**
- Go to Resend dashboard → **Broadcasts** → Create email (this will prompt verification)
- Or try sending a test email - Resend will automatically send a verification email
- Check the inbox for `project.clearpay@gmail.com` and click verification link
- After verification, try sending again

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

