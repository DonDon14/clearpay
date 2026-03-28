# 🔧 Render + Brevo Email Setup

## ⚠️ Issue: Email Works on Localhost but Fails on Render

**Problem**: Email sending works on localhost but fails on Render with error "Sender not valid" or SMTP connection errors.

**Root Cause**: Render environment variables are not configured for Brevo, or database settings are not being used.

---

## ✅ Solution: Update Render Environment Variables

### Option 1: Update via Render Dashboard (Recommended)

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Select your service**: `clearpay-web-dev-k3h3`
3. **Go to**: **Environment** tab
4. **Update these environment variables**:

   ```
   email.SMTPHost = smtp-relay.brevo.com
   email.SMTPUser = 9b7dd5001@smtp-brevo.com
   email.SMTPPass = [Your Brevo SMTP Key - get from brevo-smtp-key.txt]
   email.SMTPPort = 587
   email.SMTPCrypto = tls
   email.fromEmail = project.clearpay@gmail.com
   email.fromName = ClearPay
   email.protocol = smtp
   email.mailType = html
   ```
   
   **⚠️ IMPORTANT**: Replace `[Your Brevo SMTP Key]` with the actual key from `brevo-smtp-key.txt` (do NOT commit the actual key to git)

5. **Save** the changes
6. **Redeploy** the service:
   - Click **"Manual Deploy"** → **"Deploy latest commit"**
   - Wait 2-5 minutes for deployment

### Option 2: Use Admin Panel (Database Settings)

**If you've already saved settings via admin panel:**

1. **Login to your Render deployment**
2. **Go to**: Settings → SMTP Configuration
3. **Verify settings are saved**:
   - SMTP Host: `smtp-relay.brevo.com`
   - SMTP User: `9b7dd5001@smtp-brevo.com`
   - SMTP Password: `[Your Brevo SMTP key]`
   - Port: `587`
   - Encryption: `TLS`
   - From Email: `project.clearpay@gmail.com`

4. **The application uses database settings first**, so if they're saved, they should work
5. **If still failing**: Check Render logs for errors

---

## 🔍 How the Application Loads Email Settings

**Priority Order** (highest to lowest):

1. **Database** (`email_settings` table) - **Highest Priority**
   - Settings saved via admin panel
   - Used if table exists and has active settings

2. **Environment Variables** (Render dashboard)
   - Used if database settings not found
   - Format: `email.SMTPHost`, `email.SMTPUser`, etc.

3. **Default Values** (`app/Config/Email.php`)
   - Fallback if neither database nor environment variables are set

---

## ✅ Verification Steps

### Step 1: Check Render Environment Variables

1. **Go to Render Dashboard** → Your service → **Environment**
2. **Verify these are set**:
   - `email.SMTPHost` = `smtp-relay.brevo.com`
   - `email.SMTPUser` = `9b7dd5001@smtp-brevo.com`
   - `email.SMTPPass` = `[Your Brevo SMTP key]` (should be masked)

### Step 2: Check Database Settings (via Admin Panel)

1. **Login to your Render deployment**
2. **Go to**: Settings → SMTP Configuration
3. **Check if settings are saved** in the database
4. **If not saved**: Enter and save the Brevo settings

### Step 3: Check Brevo Sender Verification

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: Settings → Senders
3. **Verify**: `project.clearpay@gmail.com` shows **"Verified"** ✅

### Step 4: Test Email

1. **After updating settings**, wait 2-5 minutes
2. **Redeploy Render service** (if you changed environment variables)
3. **Send test email** from admin panel
4. **Check Render logs** for any errors

---

## 🚨 Common Issues

### Issue 1: Environment Variables Not Set

**Symptom**: Email fails with "SMTP configuration incomplete"

**Solution**: Set environment variables in Render dashboard (see Option 1 above)

### Issue 2: Database Settings Override Environment Variables

**Symptom**: Environment variables are set but database has different (old) settings

**Solution**: 
- Update settings via admin panel, OR
- Clear database settings (set `is_active = false` in `email_settings` table)

### Issue 3: Render Free Tier SMTP Port Blocking

**Symptom**: Connection timeout or "Unable to connect to SMTP server"

**Solution**: 
- Brevo uses port 587 (TLS), which should work on Render
- If still blocked, try port 465 (SSL) - update `email.SMTPPort = 465` and `email.SMTPCrypto = ssl`

### Issue 4: Sender Not Verified in Brevo

**Symptom**: "Sender not valid" error

**Solution**: Verify sender in Brevo → Settings → Senders (see `BREVO_SENDER_VERIFICATION_CHECKLIST.md`)

---

## 📝 Quick Checklist

Before testing on Render:

- [ ] Brevo sender `project.clearpay@gmail.com` is **Verified** ✅ in Brevo
- [ ] Render environment variables are set for Brevo (not Resend)
- [ ] `email.SMTPPass` is set in Render dashboard (the Brevo SMTP key)
- [ ] Render service was redeployed after changing environment variables
- [ ] Database settings (if used) match Brevo configuration
- [ ] Test email sent from admin panel

---

## 🔄 After Making Changes

1. **Save environment variables** in Render dashboard
2. **Redeploy service**: Manual Deploy → Deploy latest commit
3. **Wait 2-5 minutes** for deployment
4. **Test email** from admin panel
5. **Check Render logs** if still failing

---

## 📊 Render Logs

**To check logs**:
1. **Go to Render Dashboard** → Your service
2. **Click**: **"Logs"** tab
3. **Look for**:
   - SMTP connection errors
   - "Sender not valid" errors
   - Configuration loading messages

**Common log messages**:
- `"SMTP configuration incomplete"` = Missing environment variables
- `"Unable to connect to SMTP server"` = Port blocking or network issue
- `"Sender not valid"` = Sender not verified in Brevo

