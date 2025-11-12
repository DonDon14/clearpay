# 🚀 ClearPay - Render.com Deployment Guide

Complete step-by-step guide to deploy your ClearPay application to Render.com cloud platform.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Understanding Render.com](#understanding-rendercom)
3. [Pre-Deployment Preparation](#pre-deployment-preparation)
4. [Create Render Account & Connect Repository](#create-render-account--connect-repository)
5. [Deploy Web Service](#deploy-web-service)
6. [Set Up Database](#set-up-database)
7. [Configure Environment Variables](#configure-environment-variables)
8. [Run Database Migrations](#run-database-migrations)
9. [Custom Domain & SSL](#custom-domain--ssl)
10. [Testing & Verification](#testing--verification)
11. [Troubleshooting](#troubleshooting)
12. [Post-Deployment](#post-deployment)

---

## ✅ Prerequisites

Before starting, ensure you have:

- ✅ GitHub, GitLab, or Bitbucket account
- ✅ Your ClearPay code in a Git repository
- ✅ Render.com account (free tier available)
- ✅ Basic understanding of Git
- ✅ Database credentials ready (if using external database)

**System Requirements:**
- PHP 8.1 or higher (Render supports this)
- MySQL or PostgreSQL database
- Composer dependencies

---

## 🌐 Understanding Render.com

### What is Render.com?

Render is a cloud platform that automatically builds and deploys your applications. Key features:

- **Automatic Deployments:** Deploys on every Git push
- **Free Tier Available:** Great for testing and small projects
- **Managed Databases:** MySQL and PostgreSQL available
- **Free SSL:** Automatic SSL certificates
- **Custom Domains:** Easy domain configuration
- **Environment Variables:** Secure configuration management

### Render Service Types

- **Web Service:** Your PHP application
- **Database:** MySQL or PostgreSQL
- **Background Worker:** For scheduled tasks (optional)

---

## 🔧 Pre-Deployment Preparation

### Step 1: Prepare Your Repository

1. **Ensure your code is in Git:**
   ```bash
   git status
   git add .
   git commit -m "Prepare for Render deployment"
   git push origin main
   ```

2. **Verify these files exist:**
   - `composer.json` ✅
   - `public/index.php` ✅
   - `app/` directory ✅
   - `render.yaml` (we'll create this) ✅

3. **Important:** Do NOT commit `.env` file
   - It's already in `.gitignore`
   - We'll use Render's environment variables instead

### Step 2: Review Configuration Files

The following files have been created for Render:
- `render.yaml` - Render service configuration
- `render-build.sh` - Build script
- `render-start.sh` - Start script

---

## 🔐 Create Render Account & Connect Repository

### Step 1: Sign Up for Render

1. **Visit Render.com:**
   - Go to https://render.com
   - Click **Get Started for Free**

2. **Sign Up:**
   - Choose **Sign up with GitHub** (recommended)
   - Or use email signup
   - Authorize Render to access your repositories

3. **Verify Email:**
   - Check your email and verify your account

### Step 2: Connect Your Repository

1. **In Render Dashboard:**
   - Click **New +** button
   - Select **Blueprint** (if using render.yaml) OR
   - Select **Web Service** (for manual setup)

2. **Connect Repository:**
   - Click **Connect account** (GitHub/GitLab/Bitbucket)
   - Select your ClearPay repository
   - Choose the branch (usually `main` or `master`)

---

## 🚀 Deploy Web Service

### Option A: Using render.yaml (Recommended)

If you have `render.yaml` in your repository:

1. **Create Blueprint:**
   - In Render Dashboard, click **New +**
   - Select **Blueprint**
   - Connect your repository
   - Render will detect `render.yaml` automatically

2. **Review Configuration:**
   - Render will show services from `render.yaml`
   - Review the settings:
     - Service name: `clearpay-web`
     - Region: Choose closest to your users
     - Plan: `starter` (free tier) or higher

3. **Apply Blueprint:**
   - Click **Apply**
   - Render will create the web service and database

### Option B: Manual Setup

If not using `render.yaml`:

1. **Create Web Service:**
   - Click **New +** → **Web Service**
   - Connect your repository
   - Select branch: `main` or `master`

2. **Configure Service:**

   **Basic Settings:**
   - **Name:** `clearpay-web`
   - **Region:** Choose closest region (e.g., `Oregon`, `Singapore`)
   - **Branch:** `main` or `master`
   - **Root Directory:** Leave empty (or `./` if needed)
   - **Environment:** `PHP`

   **Build & Deploy:**
   - **Build Command:**
     ```bash
     composer install --no-dev --optimize-autoloader && php spark key:generate --force
     ```
   - **Start Command:**
     ```bash
     php -S 0.0.0.0:$PORT -t public public/index.php
     ```
   - **Health Check Path:** `/`

3. **Advanced Settings:**
   - **Plan:** `Starter` (free) or higher
   - **Auto-Deploy:** `Yes` (deploys on every push)

4. **Create Service:**
   - Click **Create Web Service**
   - Render will start building

---

## 🗄️ Set Up Database

### Step 1: Create Database

1. **In Render Dashboard:**
   - Click **New +**
   - Select **MySQL** (or **PostgreSQL** if preferred)

2. **Configure Database:**
   - **Name:** `clearpay-db`
   - **Database Name:** `clearpaydb`
   - **User:** `clearpay_user` (or auto-generated)
   - **Region:** Same as web service
   - **Plan:** `Starter` (free) or higher

3. **Create Database:**
   - Click **Create Database**
   - Wait for database to be provisioned

### Step 2: Get Database Credentials

1. **Access Database:**
   - Click on your database service
   - Go to **Info** tab
   - Note the following:
     - **Internal Database URL** (for same-region services)
     - **External Connection String** (for external access)
     - **Hostname**
     - **Port**
     - **Database Name**
     - **Username**
     - **Password**

2. **Connection Details:**
   - Render provides connection strings
   - For MySQL: Usually `mysql://user:password@host:port/database`
   - Parse these for your `.env` configuration

---

## ⚙️ Configure Environment Variables

### Step 1: Add Environment Variables

1. **In Web Service:**
   - Go to your web service
   - Click **Environment** tab
   - Click **Add Environment Variable**

2. **Add Required Variables:**

   **Application Settings:**
   ```
   CI_ENVIRONMENT = production
   APP_TIMEZONE = Asia/Manila
   ```

   **Base URL:**
   ```
   APP_BASE_URL = https://your-service-name.onrender.com/
   ```
   (Update after getting your Render URL)

   **Database Configuration:**
   ```
   DB_HOST = your-db-host.render.com
   DB_PORT = 3306
   DB_NAME = clearpaydb
   DB_USER = clearpay_user
   DB_PASSWORD = your-db-password
   DB_DRIVER = MySQLi
   ```

   **Encryption Key:**
   ```
   ENCRYPTION_KEY = base64:your-generated-key-here
   ```
   (Generate using: `php spark key:generate`)

   **Email Configuration (Optional):**
   ```
   EMAIL_FROM = project.clearpay@gmail.com
   EMAIL_FROM_NAME = ClearPay
   EMAIL_SMTP_HOST = smtp.gmail.com
   EMAIL_SMTP_USER = project.clearpay@gmail.com
   EMAIL_SMTP_PASS = your-app-password
   EMAIL_SMTP_PORT = 587
   EMAIL_SMTP_CRYPTO = tls
   ```

### Step 2: Link Database to Web Service

1. **In Web Service Environment:**
   - Scroll to **Database** section
   - Click **Link Database**
   - Select your `clearpay-db` database
   - Render will add `DATABASE_URL` automatically

### Step 3: Update Database Configuration

Render provides `DATABASE_URL`, but CodeIgniter needs individual values. Update your `.env` or create a script to parse it.

**Option 1: Use Individual Variables (Recommended)**

Add these to your web service environment:
```
DB_HOST = (from database info)
DB_PORT = 3306
DB_NAME = clearpaydb
DB_USER = (from database info)
DB_PASSWORD = (from database info)
```

**Option 2: Parse DATABASE_URL**

Create a script to parse `DATABASE_URL` and set individual variables.

---

## 🔄 Run Database Migrations

### Step 1: Access Render Shell

1. **In Web Service:**
   - Go to your web service
   - Click **Shell** tab
   - This opens a terminal

2. **Run Migrations:**
   ```bash
   php spark migrate
   ```

3. **Run Seeders:**
   ```bash
   php spark db:seed DatabaseSeeder
   ```

### Step 2: Verify Database

1. **Check Tables:**
   ```bash
   php spark db:table
   ```

2. **Test Connection:**
   - Visit your application URL
   - Try logging in
   - Check for database errors

---

## 🌍 Custom Domain & SSL

### Step 1: Add Custom Domain

1. **In Web Service:**
   - Go to **Settings** tab
   - Scroll to **Custom Domains**
   - Click **Add Custom Domain**

2. **Enter Domain:**
   - Enter your domain (e.g., `clearpay.com`)
   - Click **Save**

3. **Update DNS:**
   - Render will provide DNS records
   - Add CNAME record in your DNS provider:
     ```
     Type: CNAME
     Name: @ (or www)
     Value: your-service-name.onrender.com
     ```

### Step 2: SSL Certificate

1. **Automatic SSL:**
   - Render automatically provisions SSL
   - Wait 5-10 minutes after DNS update
   - SSL will be active automatically

2. **Verify SSL:**
   - Visit `https://yourdomain.com`
   - Check for green padlock

3. **Update Base URL:**
   - Update `APP_BASE_URL` environment variable
   - Use your custom domain with HTTPS

---

## ✅ Testing & Verification

### Step 1: Basic Tests

1. **Homepage:**
   - Visit your Render URL
   - Should load without errors

2. **Database Connection:**
   - Try logging in
   - Check for database errors

3. **File Uploads:**
   - Test file upload functionality
   - Verify `writable/uploads/` permissions

4. **Email (if configured):**
   - Test password reset
   - Verify email sending

### Step 2: Check Logs

1. **View Logs:**
   - In web service, click **Logs** tab
   - Check for errors or warnings
   - Monitor during testing

2. **Common Issues:**
   - Database connection errors
   - Missing environment variables
   - Permission issues
   - Missing dependencies

---

## 🔧 Troubleshooting

### Issue: Build Fails

**Symptoms:**
- Build process fails
- Error in build logs

**Solutions:**
1. Check build command in service settings
2. Verify `composer.json` is correct
3. Check PHP version compatibility
4. Review build logs for specific errors

### Issue: Application Won't Start

**Symptoms:**
- Service shows "Unhealthy"
- 502 Bad Gateway error

**Solutions:**
1. Check start command:
   ```bash
   php -S 0.0.0.0:$PORT -t public public/index.php
   ```
2. Verify `public/index.php` exists
3. Check environment variables
4. Review service logs

### Issue: Database Connection Failed

**Symptoms:**
- Database connection errors
- "Access denied" errors

**Solutions:**
1. Verify database credentials in environment variables
2. Check database is in same region as web service
3. Use internal database URL (not external)
4. Verify database user has proper permissions
5. Check database is running

### Issue: 404 Errors

**Symptoms:**
- Routes not working
- 404 for all pages

**Solutions:**
1. Check `.htaccess` file exists in `public/`
2. Verify start command uses correct document root
3. Check base URL configuration
4. Review routing configuration

### Issue: File Upload Not Working

**Symptoms:**
- Uploads fail
- Permission errors

**Solutions:**
1. Ensure `writable/uploads/` exists
2. Check file permissions (Render handles this)
3. Verify upload path in configuration
4. Check PHP `upload_max_filesize` settings

### Issue: Environment Variables Not Working

**Symptoms:**
- Variables not recognized
- Default values used

**Solutions:**
1. Verify variable names match exactly
2. Check for typos in variable names
3. Restart service after adding variables
4. Use Render's environment variable format

---

## 📊 Post-Deployment

### Step 1: Monitor Application

1. **Check Service Status:**
   - Monitor service health
   - Check uptime statistics
   - Review error rates

2. **Monitor Logs:**
   - Regularly check logs
   - Set up alerts for errors
   - Monitor performance

### Step 2: Set Up Backups

1. **Database Backups:**
   - Render provides automatic backups
   - Configure backup schedule
   - Test restore process

2. **Code Backups:**
   - Keep Git repository updated
   - Tag releases
   - Document changes

### Step 3: Performance Optimization

1. **Caching:**
   - Enable application caching
   - Use Render's caching features
   - Optimize database queries

2. **CDN (Optional):**
   - Use CDN for static assets
   - Optimize images
   - Minify CSS/JS

### Step 4: Security

1. **Environment Variables:**
   - Never commit `.env` to Git
   - Use Render's secure environment variables
   - Rotate keys regularly

2. **HTTPS:**
   - Always use HTTPS
   - Enable HSTS headers
   - Keep SSL certificates updated

3. **Updates:**
   - Keep dependencies updated
   - Monitor security advisories
   - Apply patches promptly

---

## 📝 Important Notes

### Render Free Tier Limitations

- **Sleep After Inactivity:** Free tier services sleep after 15 minutes of inactivity
- **Cold Start:** First request after sleep may be slow (30-60 seconds)
- **Upgrade:** Consider paid plan for production to avoid sleep

### Database Considerations

- **MySQL vs PostgreSQL:** Render supports both
- **Internal vs External:** Use internal connection for same-region services
- **Backups:** Configure automatic backups
- **Scaling:** Plan for database scaling as needed

### Environment Variables

- **Case Sensitive:** Variable names are case-sensitive
- **Restart Required:** Service restarts after adding variables
- **Secrets:** Never expose sensitive data in logs

### Auto-Deploy

- **Automatic:** Render deploys on every push to main branch
- **Manual Deploy:** Can trigger manual deploys
- **Rollback:** Can rollback to previous deployment

---

## 🎉 Deployment Complete!

Your ClearPay application should now be live on Render.com!

**Next Steps:**
1. Test all functionality
2. Set up monitoring
3. Configure backups
4. Add custom domain (optional)
5. Monitor performance

---

## 📞 Getting Help

### Render Support

- **Documentation:** https://render.com/docs
- **Community:** https://community.render.com
- **Support:** Available in dashboard

### CodeIgniter Resources

- **Documentation:** https://codeigniter.com/user_guide/
- **Forum:** https://forum.codeigniter.com/

---

## 🔗 Quick Reference

### Render URLs

- **Dashboard:** https://dashboard.render.com
- **Documentation:** https://render.com/docs
- **Status:** https://status.render.com

### Common Commands

```bash
# Access Render Shell
# Via Dashboard → Service → Shell tab

# Run migrations
php spark migrate

# Run seeders
php spark db:seed DatabaseSeeder

# Generate encryption key
php spark key:generate

# Check database tables
php spark db:table
```

---

**Last Updated:** 2024
**Version:** 1.0

