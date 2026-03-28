# 🔍 Debug Render Email Issue

## Current Error

**Error**: `500 (Internal Server Error)` - "Unable to send email using settings:1225 SMTP"

## Possible Causes

### 1. Database Connection Issue ✅ **LIKELY**

**If database connection fails when loading email settings:**
- Application tries to load from `email_settings` table
- If database connection fails, falls back to environment variables
- If environment variables aren't set, uses defaults (Gmail, not Brevo)

**Check Render Logs for:**
- `"Database connection failed in getEmailConfig"`
- `"Database connection successful in getEmailConfig"`
- `"Found email settings in database"`
- `"No active email settings found in database"`
- `"Using config/environment variables"`

### 2. Environment Variables Not Set

**Check Render Dashboard:**
1. Go to: **Environment** tab
2. Verify these are set:
   - `email.SMTPHost` = `smtp-relay.brevo.com`
   - `email.SMTPUser` = `9b7dd5001@smtp-brevo.com`
   - `email.SMTPPass` = `[Your Brevo SMTP key]` ⚠️ **MOST IMPORTANT**
   - `email.SMTPPort` = `587`
   - `email.SMTPCrypto` = `tls`

### 3. Database Settings Override Environment Variables

**If you saved settings via admin panel:**
- Database settings take priority over environment variables
- Check if database has old/wrong settings:
  - Go to admin panel → Settings → SMTP Configuration
  - Check what's saved
  - If wrong, update and save

### 4. SMTP Password Empty

**Most common issue:**
- `email.SMTPPass` is not set in Render environment variables
- Or database has empty password
- Check logs for: `"Pass: EMPTY"` or `"Pass: SET (0 chars)"`

---

## 🔍 How to Debug

### Step 1: Check Render Logs

1. **Go to Render Dashboard** → Your service → **Logs**
2. **Send a test email** from admin panel
3. **Look for these log messages** (I just added them):
   - `"Database connection successful in getEmailConfig"` or `"Database connection failed"`
   - `"Found email settings in database"` or `"No active email settings found"`
   - `"Using config/environment variables"`
   - `"Host: ... User: ... Pass: SET/EMPTY"`

### Step 2: Check Environment Variables

1. **Go to Render Dashboard** → Your service → **Environment**
2. **Verify `email.SMTPPass` is set**:
   - Should show as masked (dots)
   - Value should be your Brevo SMTP key
   - If not set, add it: `[Get from brevo-smtp-key.txt - DO NOT commit the actual key]`

### Step 3: Check Database Settings

1. **Login to your Render deployment**
2. **Go to**: Settings → SMTP Configuration
3. **Check what's saved**:
   - If settings are saved, they take priority
   - If wrong, update and save
   - Or clear database settings to use environment variables

### Step 4: Test Database Connection

**The logs will now show:**
- Whether database connection is working
- Whether email settings are found in database
- What values are being used (database vs environment)

---

## ✅ Quick Fix Checklist

- [ ] Check Render logs for database connection status
- [ ] Verify `email.SMTPPass` is set in Render environment variables
- [ ] Check if database has email settings saved (admin panel)
- [ ] If database settings exist, verify they're correct (Brevo, not Gmail/Resend)
- [ ] Redeploy Render service after making changes

---

## 🚨 Most Likely Issue

**`email.SMTPPass` is not set in Render environment variables**

**Fix:**
1. Go to Render Dashboard → Environment
2. Add/Update: `email.SMTPPass` = `[Your Brevo SMTP key]`
3. Save and redeploy

---

## 📊 What the Logs Will Tell You

After sending a test email, check Render logs for:

**If database connection works:**
```
Database connection successful in getEmailConfig
email_settings table exists, querying for active settings
Found email settings in database - Host: smtp-relay.brevo.com, User: 9b7dd5001@smtp-brevo.com, Pass: SET (XX chars)
```

**If database connection fails:**
```
Database connection failed in getEmailConfig, using environment variables: [error message]
Using config/environment variables - Host: smtp-relay.brevo.com, User: 9b7dd5001@smtp-brevo.com, Pass: SET/EMPTY
```

**If environment variables not set:**
```
Using config/environment variables - Host: smtp.gmail.com, User: project.clearpay@gmail.com, Pass: EMPTY
```
(This means it's using defaults, not Brevo!)

---

## 🔧 Next Steps

1. **Check Render logs** after sending test email
2. **Share the log output** - it will show exactly what's happening
3. **Based on logs**, we can fix the exact issue

