# Fix Email Sending on Render

## Issue
Email sending is failing on Render deployment with 500 Internal Server Error.

## Root Causes
1. **Gmail App Password with spaces**: The password `jdab pewu hoqn whho` has spaces that need to be removed
2. **Code not deployed**: The latest code changes haven't been deployed to Render yet
3. **Environment variables**: The password in Render dashboard might have spaces

## Solution Steps

### Step 1: Update Render Environment Variables

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Navigate to your service**: `clearpay-web-dev`
3. **Go to Environment tab**
4. **Find `email.SMTPPass` variable**
5. **Update the password** to remove spaces:
   - **Old**: `jdab pewu hoqn whho` (with spaces)
   - **New**: `jdabpewuhoqnwhho` (without spaces)
6. **Save changes**

### Step 2: Trigger Redeploy

After updating environment variables, Render should auto-redeploy. If not:

1. **Go to your service in Render dashboard**
2. **Click "Manual Deploy"** → **"Deploy latest commit"**
3. **Wait for deployment to complete** (usually 2-5 minutes)

### Step 3: Verify Code is Deployed

The latest commit (`72c4718`) includes:
- ✅ Gmail App Password space removal fix
- ✅ Better error handling
- ✅ OpenSSL checks

### Step 4: Test Email Again

1. **Go to Settings page** on Render
2. **Click "Configure"** on SMTP Configuration
3. **Verify password is correct** (use toggle to see it)
4. **Click "Save Configuration"**
5. **Click "Send Test"**

## Alternative: Update Password via Database

If you prefer to update via the admin panel:

1. **Login to Render deployment**
2. **Go to Settings** → **SMTP Configuration**
3. **Enter password WITHOUT spaces**: `jdabpewuhoqnwhho`
4. **Save Configuration**
5. **Send Test Email**

## Why This Happens

Gmail App Passwords are displayed with spaces for readability (e.g., `jdab pewu hoqn whho`), but:
- Gmail accepts them **with or without spaces**
- Some SMTP libraries work better **without spaces**
- Our code now automatically removes spaces, but if the password is stored in Render environment variables with spaces, it needs to be updated there

## Verification

After fixing, you should see:
- ✅ Test email sends successfully
- ✅ No 500 errors
- ✅ Email received in inbox

## If Still Failing

Check Render logs:
1. **Go to Render dashboard** → **Your service** → **Logs**
2. **Look for email-related errors**
3. **Check for OpenSSL errors** (should be fixed in latest code)
4. **Verify SMTP credentials are correct**

---

**Note**: The code fix (removing spaces) is already in the latest commit. Once Render redeploys with the new code, emails should work even if the password has spaces in environment variables. However, it's still recommended to remove spaces from the environment variable for consistency.

