# 🚀 ClearPay - Hostinger Deployment Guide

Complete step-by-step guide to deploy your ClearPay application to Hostinger hosting.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Pre-Deployment Preparation](#pre-deployment-preparation)
3. [Hostinger Setup](#hostinger-setup)
4. [Upload Files](#upload-files)
5. [Database Setup](#database-setup)
6. [Environment Configuration](#environment-configuration)
7. [Install Dependencies](#install-dependencies)
8. [File Permissions](#file-permissions)
9. [SSL Certificate Setup](#ssl-certificate-setup)
10. [Final Configuration](#final-configuration)
11. [Testing & Verification](#testing--verification)
12. [Troubleshooting](#troubleshooting)

---

## ✅ Prerequisites

Before starting, ensure you have:

- ✅ Hostinger hosting account (any plan that supports PHP 8.1+)
- ✅ Domain name connected to Hostinger
- ✅ FTP/SFTP credentials or access to hPanel File Manager
- ✅ SSH access (optional, but recommended for Composer)
- ✅ Database credentials from Hostinger
- ✅ Local copy of your ClearPay application

**System Requirements:**
- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache with mod_rewrite enabled
- Composer (for dependency management)

---

## 🔧 Pre-Deployment Preparation

### Step 1: Prepare Your Local Files

1. **Clean up unnecessary files:**
   - Remove any test files, temporary files, or development-only files
   - Ensure `.env` is NOT included (it's in `.gitignore`)

2. **Verify your code is production-ready:**
   ```bash
   # Check for any hardcoded localhost URLs
   # Check for debug mode settings
   # Ensure error reporting is disabled in production
   ```

3. **Create a deployment package:**
   - Compress all files except:
     - `.env` (create on server)
     - `vendor/` (install on server if SSH available)
     - `writable/cache/*` (empty cache)
     - `writable/logs/*` (empty logs)
     - `writable/session/*` (empty sessions)

---

## 🌐 Hostinger Setup

### Step 1: Access hPanel

1. Log in to your Hostinger account
2. Navigate to **hPanel** (Hostinger Control Panel)
3. Select your domain

### Step 2: Check PHP Version

1. Go to **Advanced** → **PHP Configuration**
2. Select **PHP 8.1** or higher (recommended: PHP 8.2)
3. Enable required PHP extensions:
   - `mysqli`
   - `mbstring`
   - `openssl`
   - `curl`
   - `zip`
   - `gd` (for image processing)
   - `fileinfo`

### Step 3: Set Document Root

**IMPORTANT:** Hostinger allows you to change the document root. Set it to point to the `public/` folder.

1. Go to **Advanced** → **File Manager**
2. Navigate to your domain's root directory (usually `public_html/`)
3. **Option A - Standard Structure (Recommended):**
   - Upload entire project to `public_html/`
   - Set document root to `public_html/public/` in hPanel
   - Or create a subdomain and point it to `public_html/public/`

   **Option B - If document root cannot be changed:**
   - Use the flat structure approach (see Alternative Structure below)

---

## 📤 Upload Files

### Method 1: Using File Manager (hPanel)

1. **Access File Manager:**
   - Go to **Files** → **File Manager** in hPanel
   - Navigate to `public_html/`

2. **Upload files:**
   - Click **Upload** button
   - Select all files from your local project
   - Wait for upload to complete

3. **Extract if needed:**
   - If you uploaded a ZIP file, right-click and select **Extract**

### Method 2: Using FTP/SFTP

1. **Get FTP credentials:**
   - Go to **Files** → **FTP Accounts** in hPanel
   - Note your FTP host, username, and password

2. **Connect using FTP client:**
   - Use FileZilla, WinSCP, or similar
   - Connect to your Hostinger server
   - Navigate to `public_html/`
   - Upload all files maintaining the directory structure

### Recommended Folder Structure

```
public_html/
├── .htaccess          (from public/.htaccess)
├── index.php          (from public/index.php)
├── favicon.ico        (from public/favicon.ico)
├── robots.txt         (from public/robots.txt)
├── css/               (from public/css/)
├── js/                (from public/js/)
├── uploads/           (from public/uploads/)
├── app/               (entire app/ folder)
├── vendor/            (will be installed or uploaded)
├── writable/          (entire writable/ folder)
├── spark              (spark file)
├── composer.json
├── composer.lock
└── .env               (create on server - DO NOT upload)
```

**OR (Standard Structure - Recommended):**

```
public_html/
├── public/             (set document root here)
│   ├── .htaccess
│   ├── index.php
│   ├── favicon.ico
│   ├── robots.txt
│   ├── css/
│   ├── js/
│   └── uploads/
├── app/
├── vendor/
├── writable/
├── spark
├── composer.json
├── composer.lock
└── .env
```

---

## 🗄️ Database Setup

### Step 1: Create Database

1. **Access MySQL Databases:**
   - Go to **Databases** → **MySQL Databases** in hPanel

2. **Create Database:**
   - Enter database name (e.g., `clearpaydb`)
   - Click **Create**
   - Note the full database name (usually `username_clearpaydb`)

3. **Create Database User:**
   - Scroll to **MySQL Users** section
   - Enter username and strong password
   - Click **Create User**

4. **Assign User to Database:**
   - Scroll to **Add User to Database**
   - Select the user and database
   - Click **Add**
   - Grant **ALL PRIVILEGES**
   - Click **Make Changes**

### Step 2: Import Database (if you have existing data)

1. **Access phpMyAdmin:**
   - Go to **Databases** → **phpMyAdmin** in hPanel
   - Select your database

2. **Import SQL file:**
   - Click **Import** tab
   - Choose your SQL file
   - Click **Go**

**OR use migrations:**
```bash
php spark migrate
php spark db:seed
```

---

## ⚙️ Environment Configuration

### Step 1: Create .env File

1. **Access File Manager or SSH:**
   - Navigate to your project root (`public_html/` or where you uploaded files)

2. **Create .env file:**
   - Create a new file named `.env`
   - Copy the contents from `.env.example` (see below)

3. **Update with your values:**

```env
# Environment
CI_ENVIRONMENT = production

# Base URL - IMPORTANT: Update with your domain
app.baseURL = 'https://yourdomain.com/'
app.appTimezone = 'Asia/Manila'

# Security - Generate encryption key
# Run: php spark key:generate
encryption.key = base64:YOUR_GENERATED_KEY_HERE

# Database Configuration
database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_database_user
database.default.password = your_database_password
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.DBDebug = false
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci

# Email Configuration
email.fromEmail = 'project.clearpay@gmail.com'
email.fromName = 'ClearPay'
email.protocol = 'smtp'
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = 'project.clearpay@gmail.com'
email.SMTPPass = 'your_app_password_here'
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
email.mailType = 'html'
```

### Step 2: Generate Encryption Key

**If you have SSH access:**
```bash
cd /home/username/public_html
php spark key:generate
```

**If you don't have SSH access:**
1. Generate key locally:
   ```bash
   php spark key:generate
   ```
2. Copy the generated key
3. Paste it into `.env` file on server

**OR manually generate:**
```bash
php -r "echo 'base64:' . base64_encode(random_bytes(32));"
```

---

## 📦 Install Dependencies

### Option 1: Using SSH (Recommended)

1. **Access SSH:**
   - Go to **Advanced** → **SSH Access** in hPanel
   - Enable SSH if not already enabled
   - Note your SSH credentials

2. **Connect via SSH:**
   ```bash
   ssh username@yourdomain.com
   ```

3. **Navigate to project:**
   ```bash
   cd public_html
   ```

4. **Install Composer dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### Option 2: Upload vendor/ Folder

If SSH is not available:

1. **Install locally:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

2. **Upload vendor/ folder:**
   - Compress the `vendor/` folder
   - Upload to server via FTP/File Manager
   - Extract on server

---

## 🔐 File Permissions

### Set Permissions via File Manager

1. **Navigate to your project root**
2. **Set folder permissions:**
   - `writable/` → **775** (recursive)
   - All other folders → **755**

3. **Set file permissions:**
   - All files → **644**
   - `spark` → **755** (executable)

### Set Permissions via SSH

```bash
# Navigate to project root
cd public_html

# Set writable folder permissions
chmod -R 775 writable/

# Set spark executable
chmod +x spark

# Set other folders
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

---

## 🔒 SSL Certificate Setup

### Step 1: Enable SSL

1. **Access SSL settings:**
   - Go to **Advanced** → **SSL** in hPanel

2. **Install SSL Certificate:**
   - Hostinger provides free SSL via Let's Encrypt
   - Click **Install SSL Certificate**
   - Select your domain
   - Wait for installation (usually 5-10 minutes)

### Step 2: Force HTTPS

The `.htaccess` file is already configured to redirect HTTP to HTTPS. Verify it's enabled in `public/.htaccess`:

```apache
# Force HTTPS redirect
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 🎯 Final Configuration

### Step 1: Update Base URL

Ensure your `.env` file has the correct base URL:
```env
app.baseURL = 'https://yourdomain.com/'
```

### Step 2: Run Database Migrations

**If you have SSH access:**
```bash
php spark migrate
php spark db:seed
```

**If you don't have SSH access:**
- Import your database via phpMyAdmin
- Or use a migration tool via web interface

### Step 3: Test Application

1. **Visit your domain:**
   - Open `https://yourdomain.com/` in browser
   - Check if the application loads correctly

2. **Test key features:**
   - Login functionality
   - Database connections
   - File uploads
   - Email sending

---

## ✅ Testing & Verification

### Checklist

- [ ] Application loads without errors
- [ ] Database connection works
- [ ] Login/authentication works
- [ ] File uploads work (check `writable/uploads/` permissions)
- [ ] Email sending works (test password reset)
- [ ] HTTPS is enforced
- [ ] No PHP errors in logs
- [ ] All routes work correctly
- [ ] Static assets (CSS/JS) load properly

### Check Error Logs

1. **Access error logs:**
   - Go to **Advanced** → **Error Log** in hPanel
   - Or check `writable/logs/` folder

2. **Common issues:**
   - Permission errors → Fix file permissions
   - Database connection errors → Check `.env` database settings
   - Missing vendor files → Install Composer dependencies
   - 500 errors → Check `.htaccess` and PHP version

---

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error

**Solutions:**
1. Check `.htaccess` file exists and is correct
2. Verify PHP version is 8.1+
3. Check file permissions
4. Review error logs in hPanel

### Issue: Database Connection Failed

**Solutions:**
1. Verify database credentials in `.env`
2. Check database host (usually `localhost` on Hostinger)
3. Ensure database user has proper permissions
4. Test connection via phpMyAdmin

### Issue: CSS/JS Not Loading

**Solutions:**
1. Check base URL in `.env`
2. Verify file paths in views
3. Clear browser cache
4. Check file permissions

### Issue: File Upload Not Working

**Solutions:**
1. Set `writable/uploads/` to **775** permissions
2. Check PHP `upload_max_filesize` and `post_max_size`
3. Verify upload path in configuration

### Issue: Composer Not Available

**Solutions:**
1. Request SSH access from Hostinger support
2. Or upload `vendor/` folder manually
3. Use Hostinger's built-in Composer (if available)

### Issue: Encryption Key Error

**Solutions:**
1. Generate new key: `php spark key:generate`
2. Update `.env` file with new key
3. Clear `writable/session/` folder

---

## 📞 Getting Help

### Hostinger Support

- **Live Chat:** Available 24/7 in hPanel
- **Knowledge Base:** https://support.hostinger.com/
- **Community:** https://community.hostinger.com/

### CodeIgniter Resources

- **Documentation:** https://codeigniter.com/user_guide/
- **Forum:** https://forum.codeigniter.com/

---

## 🎉 Post-Deployment

### Security Recommendations

1. **Change default passwords**
2. **Keep dependencies updated:**
   ```bash
   composer update --no-dev
   ```
3. **Regular backups:**
   - Use Hostinger's backup feature
   - Or set up automated backups
4. **Monitor error logs regularly**
5. **Keep PHP version updated**

### Performance Optimization

1. **Enable caching** (if applicable)
2. **Optimize images** before upload
3. **Use CDN** for static assets (optional)
4. **Enable Gzip compression** (usually enabled by default)

---

## 📝 Notes

- **Document Root:** Hostinger allows changing document root, so you can use the standard CodeIgniter structure with `public/` as document root
- **PHP Version:** Always use PHP 8.1 or higher for best performance and security
- **Backups:** Always backup before making changes
- **Environment:** Keep `CI_ENVIRONMENT = production` in `.env` for production

---

**Last Updated:** 2024
**Version:** 1.0

