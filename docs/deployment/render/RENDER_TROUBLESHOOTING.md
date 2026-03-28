# 🔧 Render.com Troubleshooting Guide

Quick guide to diagnose and fix issues with your ClearPay deployment on Render.

---

## 🔍 Step 1: Check Service Status

1. **Go to Render Dashboard:**
   - Visit https://dashboard.render.com
   - Find your service: `clearpay-web` (Service ID: `srv-d4a3vus9c44c73fda9cg`)

2. **Check Status:**
   - Look for status badge: **Live**, **Building**, **Failed**, or **Sleeping**
   - **Sleeping** = Free tier service spun down (normal after 15 min inactivity)
   - **Failed** = Deployment error (check logs)
   - **Building** = Still deploying (wait)

---

## 📊 Step 2: Check Logs

1. **In Render Dashboard:**
   - Click your service (`clearpay-web`)
   - Click **Logs** tab
   - Look for error messages

2. **Common Errors to Look For:**
   - Database connection errors
   - PHP fatal errors
   - Missing file errors
   - Permission errors

---

## 🐛 Common Issues & Solutions

### Issue 1: Service is "Sleeping"

**Symptoms:**
- Status shows "Sleeping"
- Site takes 30-60 seconds to respond
- First request is slow

**Solution:**
- This is **normal** for free tier
- Wait 30-60 seconds for first request
- Consider upgrading to paid plan ($7/month) for always-on service

---

### Issue 2: Service Shows "Failed"

**Symptoms:**
- Status shows "Failed"
- Deployment didn't complete
- Site shows error page

**Solution:**
1. Check **Logs** tab for build errors
2. Check **Events** tab for deployment errors
3. Common causes:
   - Docker build failed
   - Missing dependencies
   - Configuration errors

---

### Issue 3: "502 Bad Gateway" or "Application Error"

**Symptoms:**
- Site loads but shows error
- 502 error page
- "Application Error" message

**Possible Causes:**

#### A. Database Not Connected

**Check:**
- Go to **Environment** tab
- Verify `DATABASE_URL` is set
- Check database service is running

**Fix:**
- Link database in Environment tab
- Verify database credentials

#### B. Migrations Not Run

**Check Logs for:**
- "Table doesn't exist" errors
- Database connection errors

**Fix:**
- Migrations should run automatically on startup
- Check logs for migration messages
- If not running, see migration guide

#### C. PHP Errors

**Check Logs for:**
- PHP Fatal errors
- Missing class errors
- Syntax errors

**Fix:**
- Review error message
- Check code for issues
- Verify all files uploaded

---

### Issue 4: "404 Not Found"

**Symptoms:**
- Site loads but pages show 404
- Routes not working

**Possible Causes:**
- `.htaccess` not working
- Apache mod_rewrite not enabled
- Base URL misconfigured

**Fix:**
- Verify `.htaccess` exists in `public/` folder
- Check Apache configuration
- Verify base URL in environment variables

---

### Issue 5: "Database Connection Failed"

**Symptoms:**
- Error about database connection
- "Access denied" errors

**Check:**
1. **Environment Variables:**
   - Go to **Environment** tab
   - Verify `DATABASE_URL` is set
   - Or check individual DB variables

2. **Database Service:**
   - Go to database service (`clearpay-db`)
   - Verify it's "Available"
   - Check region matches web service

3. **Database Credentials:**
   - Verify username/password
   - Check database name
   - Verify host/port

**Fix:**
- Link database in Environment tab
- Update database credentials
- Verify database is in same region

---

### Issue 6: "White Screen" or Blank Page

**Symptoms:**
- Page loads but shows nothing
- Blank white screen

**Possible Causes:**
- PHP fatal error
- Missing files
- Permission issues

**Fix:**
1. Check **Logs** tab for PHP errors
2. Verify all files uploaded
3. Check file permissions
4. Enable error display (temporarily) to see errors

---

## 🔍 Diagnostic Commands

### Check Service Health

1. **Visit your URL:**
   ```
   https://clearpay-web.onrender.com
   ```

2. **Check specific endpoints:**
   ```
   https://clearpay-web.onrender.com/admin/login
   https://clearpay-web.onrender.com/payer/login
   ```

### Check Logs

1. **In Render Dashboard:**
   - Service → **Logs** tab
   - Look for recent errors
   - Check timestamps

2. **Filter Logs:**
   - Use search to find specific errors
   - Filter by time range
   - Look for "ERROR" or "FATAL"

---

## ✅ Quick Checklist

- [ ] Service status is "Live" (not "Failed" or "Sleeping")
- [ ] No errors in Logs tab
- [ ] Database service is "Available"
- [ ] Database is linked to web service
- [ ] Environment variables are set
- [ ] `DATABASE_URL` is configured
- [ ] Migrations ran (check logs)
- [ ] No PHP fatal errors in logs
- [ ] `.htaccess` file exists
- [ ] All files uploaded correctly

---

## 🆘 Still Not Working?

### Get More Information

1. **Check Full Logs:**
   - Go to **Logs** tab
   - Scroll to deployment start
   - Look for any error messages

2. **Check Events:**
   - Go to **Events** tab
   - See deployment history
   - Check for failed deployments

3. **Check Metrics:**
   - Go to **Metrics** tab
   - Check CPU/Memory usage
   - Verify service is running

### Contact Support

If still not working:
1. **Render Support:**
   - Click "Contact support" in dashboard
   - Include service ID: `srv-d4a3vus9c44c73fda9cg`
   - Include error messages from logs

2. **Check Documentation:**
   - Render docs: https://render.com/docs
   - CodeIgniter docs: https://codeigniter.com/user_guide/

---

## 📝 Common Error Messages

### "Connection refused"
- Database not accessible
- Check database service status
- Verify database region

### "Table doesn't exist"
- Migrations not run
- Check if migrations ran in logs
- Run migrations manually (if Shell available)

### "Permission denied"
- File permission issues
- Check writable/ folder permissions
- Verify file ownership

### "Class not found"
- Missing Composer dependencies
- Run `composer install`
- Check vendor/ folder exists

---

**Last Updated:** 2024

