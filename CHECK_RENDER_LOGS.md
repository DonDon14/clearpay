# 🔍 How to Check Render Application Logs

## ⚠️ Current Issue

**Error**: `500 Internal Server Error` on `POST /admin/email-settings/test-email`

The access logs show the 500 error, but we need **application logs** to see the diagnostic messages I added.

---

## 📊 Access Logs vs Application Logs

**What you showed me** (Access Logs):
- Shows HTTP requests/responses
- Shows `500` error
- **Does NOT show** application-level logs (log_message entries)

**What we need** (Application Logs):
- Shows `log_message('info', ...)` entries
- Shows `log_message('error', ...)` entries
- Shows database connection status
- Shows email configuration loading

---

## 🔍 How to Access Application Logs on Render

### Method 1: Render Dashboard Logs (Recommended)

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Select your service**: `clearpay-web-dev-k3h3`
3. **Click**: **"Logs"** tab
4. **Look for** log entries with timestamps around when you sent the test email
5. **Search for**:
   - `"Database connection"`
   - `"getEmailConfig"`
   - `"Found email settings"`
   - `"Using config/environment variables"`
   - `"Pass: SET"` or `"Pass: EMPTY"`

### Method 2: Check Log Files via Shell

1. **Go to Render Dashboard** → Your service
2. **Click**: **"Shell"** tab
3. **Run**: `tail -f writable/logs/log-$(date +%Y-%m-%d).log`
   - Or: `cat writable/logs/log-*.log | tail -50`

### Method 3: Download Logs

1. **Go to Render Dashboard** → Your service → **Logs**
2. **Scroll down** to find log entries
3. **Look for entries** around `09:56:11` (when the test email was sent)

---

## 🔍 What to Look For in Logs

After sending a test email, you should see these log messages:

### If Database Connection Works:
```
[INFO] Database connection successful in getEmailConfig
[INFO] email_settings table exists, querying for active settings
[INFO] Found email settings in database - Host: smtp-relay.brevo.com, User: 9b7dd5001@smtp-brevo.com, Pass: SET (XX chars)
```

### If Database Connection Fails:
```
[ERROR] Database connection failed in getEmailConfig, using environment variables: [error message]
[INFO] Using config/environment variables - Host: smtp-relay.brevo.com, User: 9b7dd5001@smtp-brevo.com, Pass: SET/EMPTY
```

### If Environment Variables Not Set:
```
[INFO] Using config/environment variables - Host: smtp.gmail.com, User: project.clearpay@gmail.com, Pass: EMPTY
```
(This means it's using defaults, not Brevo!)

---

## 🚨 Most Likely Issues Based on Logs

### Issue 1: Password is EMPTY
**Log shows**: `Pass: EMPTY` or `Pass: SET (0 chars)`

**Fix**: Set `email.SMTPPass` in Render environment variables

### Issue 2: Using Wrong SMTP Host
**Log shows**: `Host: smtp.gmail.com` (instead of `smtp-relay.brevo.com`)

**Fix**: Environment variables not set, using defaults

### Issue 3: Database Connection Failing
**Log shows**: `Database connection failed in getEmailConfig`

**Fix**: Check database connection string in Render

---

## 📝 Next Steps

1. **Go to Render Dashboard** → Your service → **Logs**
2. **Send a test email** from admin panel
3. **Look for log entries** around that time
4. **Search for** the keywords above
5. **Share the log output** - it will show exactly what's wrong

---

## 💡 Quick Check

**If you can't find application logs**, the issue is likely:
- `email.SMTPPass` is not set in Render environment variables
- Environment variables are using old/incorrect values

**Quick Fix**:
1. Go to Render Dashboard → Environment
2. Verify `email.SMTPPass` is set
3. If not, add it with your Brevo SMTP key
4. Redeploy

