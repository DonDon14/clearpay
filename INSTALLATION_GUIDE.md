# ClearPay - Complete Installation Guide

**Version:** 1.0  
**Last Updated:** 2025  
**PHP Requirement:** PHP 8.1 or higher

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Step 1: Install XAMPP](#step-1-install-xampp)
3. [Step 2: Configure XAMPP PHP (php.ini)](#step-2-configure-xampp-php-phpini)
4. [Step 3: Configure Apache (httpd.conf)](#step-3-configure-apache-httpdconf)
5. [Step 4: Install Composer](#step-4-install-composer)
6. [Step 5: Clone/Download Project](#step-5-clonedownload-project)
7. [Step 6: Install Dependencies](#step-6-install-dependencies)
8. [Step 7: Create .env File](#step-7-create-env-file)
9. [Step 8: Database Setup](#step-8-database-setup)
10. [Step 9: Run Migrations](#step-9-run-migrations)
11. [Step 10: Verify Installation](#step-10-verify-installation)
12. [Troubleshooting & Plan B's](#troubleshooting--plan-bs)

---

## Prerequisites

### Required Software

- **Windows 10/11** (or Windows Server)
- **XAMPP** with PHP 8.1 or higher
- **Composer** (PHP dependency manager)
- **Git** (optional, for cloning repository)
- **Text Editor** (Notepad++, VS Code, etc.)

### Required PHP Extensions

The following PHP extensions must be enabled:
- ✅ `intl` - Internationalization
- ✅ `mbstring` - Multi-byte string support
- ✅ `gd` - Image manipulation
- ✅ `mysqlnd` - MySQL native driver
- ✅ `curl` - HTTP client
- ✅ `json` - JSON support (usually enabled by default)
- ✅ `zip` - ZIP file support
- ✅ `soap` - SOAP support
- ✅ `fileinfo` - File type detection

---

## Step 1: Install XAMPP

### Plan A: Fresh Installation

1. **Download XAMPP**
   - Visit: https://www.apachefriends.org/download.html
   - Download **XAMPP for Windows** (PHP 8.1 or higher)
   - Choose the **Installer** version (not ZIP)

2. **Install XAMPP**
   - Run the installer as **Administrator** (Right-click → Run as administrator)
   - Select installation location: `C:\xampp` (default)
   - **Select Components:**
     - ✅ Apache
     - ✅ MySQL
     - ✅ PHP
     - ✅ phpMyAdmin
     - ❌ Others (optional, not required)

3. **Complete Installation**
   - Wait for installation to finish
   - When prompted about UAC, click "Yes"
   - Click "Finish"

4. **Start XAMPP Control Panel**
   - Open **XAMPP Control Panel** from Start Menu
   - Click **Start** for **Apache**
   - Click **Start** for **MySQL**
   - Both should show **green "Running"** status

5. **Verify Installation**
   - Open browser: `http://localhost`
   - You should see the XAMPP dashboard
   - Open: `http://localhost/phpmyadmin`
   - You should see phpMyAdmin login page

### Plan B: If XAMPP Won't Start

#### Apache Port Conflict (Port 80)

**Symptoms:** Apache won't start, shows error about port 80

**Solution 1: Change Apache Port**
1. Open `C:\xampp\apache\conf\httpd.conf`
2. Find: `Listen 80`
3. Change to: `Listen 8080`
4. Find: `ServerName localhost:80`
5. Change to: `ServerName localhost:8080`
6. Save and restart Apache
7. Access via: `http://localhost:8080`

**Solution 2: Stop Conflicting Service**
1. Open **Command Prompt as Administrator**
2. Run: `net stop w3svc` (stops IIS)
3. Or run: `net stop http` (stops HTTP service)
4. Restart Apache in XAMPP

**Solution 3: Use Different Ports**
1. Edit `C:\xampp\apache\conf\httpd.conf`
2. Change `Listen 80` to `Listen 8080`
3. Edit `C:\xampp\apache\conf\extra\httpd-ssl.conf`
4. Change `Listen 443` to `Listen 8443`
5. Restart Apache

#### MySQL Port Conflict (Port 3306)

**Symptoms:** MySQL won't start, shows error about port 3306

**Solution 1: Change MySQL Port**
1. Open `C:\xampp\mysql\bin\my.ini`
2. Find: `port=3306`
3. Change to: `port=3307`
4. Also change in `C:\xampp\apache\conf\extra\httpd-xampp.conf`
5. Find: `phpMyAdmin` section and change port to 3307
6. Restart MySQL

**Solution 2: Stop Conflicting MySQL Service**
1. Open **Command Prompt as Administrator**
2. Run: `net stop mysql80` (or your MySQL service name)
3. Check services: `services.msc`
4. Find MySQL service and stop it
5. Restart MySQL in XAMPP

---

## Step 2: Configure XAMPP PHP (php.ini)

### Locate php.ini File

**Location:** `C:\xampp\php\php.ini`

**How to Find:**
1. Open XAMPP Control Panel
2. Click **Config** button next to Apache
3. Select **PHP (php.ini)**
4. This opens the file in Notepad

### Required PHP Configuration Changes

#### 1. Enable Required Extensions

**Find and uncomment (remove `;` at the beginning):**

```ini
; Line ~930 (approximately)
extension=intl
extension=mbstring
extension=gd
extension=curl
extension=zip
extension=soap
extension=fileinfo
```

**How to find:**
- Press `Ctrl + F` in Notepad
- Search for: `;extension=intl`
- Remove the `;` semicolon at the beginning
- Repeat for all extensions above

**Verify Extensions:**
- Create a file: `C:\xampp\htdocs\phpinfo.php`
- Add: `<?php phpinfo(); ?>`
- Open: `http://localhost/phpinfo.php`
- Search for each extension name (Ctrl+F)
- Should show "enabled" or display configuration

#### 2. Configure File Upload Settings

**Find these lines and set values:**

```ini
; Line ~783 (approximately)
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
```

**Why these values:**
- `upload_max_filesize = 10M` - Maximum file upload size (app uses 2MB, but 10MB gives buffer)
- `post_max_size = 10M` - Must be >= upload_max_filesize
- `memory_limit = 256M` - Memory for PHP scripts (increased for image processing)
- `max_execution_time = 300` - Script execution time (5 minutes)
- `max_input_time = 300` - Time to parse input data

#### 3. Configure Date/Time Settings

```ini
; Line ~1000 (approximately)
date.timezone = Asia/Manila
```

**Or use your timezone:**
- `Asia/Manila` - Philippines
- `America/New_York` - Eastern Time
- `Europe/London` - UK
- Full list: https://www.php.net/manual/en/timezones.php

#### 4. Enable Error Reporting (Development)

```ini
; Line ~460 (approximately)
display_errors = On
error_reporting = E_ALL
```

**For Production:** Set `display_errors = Off` and log errors instead.

#### 5. Session Configuration

```ini
; Line ~1300 (approximately)
session.save_path = "C:\xampp\tmp"
session.gc_maxlifetime = 1440
```

**Verify tmp folder exists:**
- Check if `C:\xampp\tmp` exists
- If not, create it manually
- Make sure it's writable

### Save and Restart

1. **Save php.ini** (Ctrl+S)
2. **Close Notepad**
3. **Restart Apache** in XAMPP Control Panel
   - Click **Stop** on Apache
   - Wait 3-5 seconds
   - Click **Start** on Apache

### Plan B: If Extensions Don't Load

#### Problem: Extension file not found

**Solution 1: Check Extension Directory**
1. In `php.ini`, find: `extension_dir`
2. Should be: `extension_dir = "ext"`
3. Or full path: `extension_dir = "C:\xampp\php\ext"`
4. Verify `C:\xampp\php\ext` folder exists
5. Check if extension files exist (e.g., `php_intl.dll`, `php_gd.dll`)

**Solution 2: Download Missing Extensions**
1. Check PHP version: `php -v` in Command Prompt
2. Download matching extensions from: https://pecl.php.net/
3. Or reinstall XAMPP with all components

**Solution 3: Verify Extension Loading**
1. Create `test.php` in `C:\xampp\htdocs\`
2. Add: `<?php var_dump(extension_loaded('intl')); ?>`
3. Access: `http://localhost/test.php`
4. Should show: `bool(true)`
5. If `bool(false)`, extension is not loaded

---

## Step 3: Configure Apache (httpd.conf)

### Locate httpd.conf File

**Location:** `C:\xampp\apache\conf\httpd.conf`

**How to Open:**
1. Open XAMPP Control Panel
2. Click **Config** button next to Apache
3. Select **httpd.conf**

### Required Apache Configuration

#### 1. Enable mod_rewrite (Required for Clean URLs)

**Find and uncomment:**

```apache
# Line ~167 (approximately)
LoadModule rewrite_module modules/mod_rewrite.so
```

**Verify:**
- Should NOT have `#` at the beginning
- Should be: `LoadModule rewrite_module modules/mod_rewrite.so`

#### 2. Configure Directory Permissions

**Find the `<Directory>` section for your htdocs:**

```apache
# Line ~245 (approximately)
<Directory "C:/xampp/htdocs">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

**Critical Settings:**
- `AllowOverride All` - Allows .htaccess files to work
- `Require all granted` - Allows access to directory

**If you see `Require local`:**
- Change to: `Require all granted` (for development)
- Or keep `Require local` if you only access from localhost

#### 3. Set Server Name (Optional but Recommended)

```apache
# Line ~229 (approximately)
ServerName localhost:80
```

**Or if using different port:**
```apache
ServerName localhost:8080
```

#### 4. Enable .htaccess Support

**Find and verify:**

```apache
# Line ~240 (approximately)
AccessFileName .htaccess
```

**Should be uncommented (no #)**

### Save and Restart

1. **Save httpd.conf** (Ctrl+S)
2. **Restart Apache** in XAMPP Control Panel

### Plan B: If mod_rewrite Doesn't Work

#### Problem: 404 errors on routes

**Solution 1: Check .htaccess File**
1. Navigate to: `C:\xampp\htdocs\ClearPay\public\.htaccess`
2. Should contain:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```
3. If missing, create it

**Solution 2: Verify AllowOverride**
1. Check `httpd.conf` has `AllowOverride All`
2. Not `AllowOverride None`
3. Restart Apache

**Solution 3: Check Apache Error Log**
1. Location: `C:\xampp\apache\logs\error.log`
2. Open and check for rewrite errors
3. Look for "mod_rewrite" errors

---

## Step 4: Install Composer

### Plan A: Windows Installer

1. **Download Composer**
   - Visit: https://getcomposer.org/download/
   - Click **Composer-Setup.exe** (Windows Installer)
   - Download the installer

2. **Run Installer**
   - Run `Composer-Setup.exe`
   - **Important:** Check "Add this PHP installation to your PATH"
   - Select PHP executable: `C:\xampp\php\php.exe`
   - Click **Next** through the wizard
   - Click **Install**

3. **Verify Installation**
   - Open **Command Prompt** or **PowerShell**
   - Run: `composer --version`
   - Should show: `Composer version X.X.X`

### Plan B: Manual Installation

#### If Installer Doesn't Work

1. **Download Composer PHAR**
   - Visit: https://getcomposer.org/download/
   - Download `composer.phar`

2. **Create Composer Batch File**
   - Create file: `C:\xampp\php\composer.bat`
   - Add content:
   ```batch
   @echo off
   php "%~dp0composer.phar" %*
   ```

3. **Add to PATH**
   - Open **System Properties** → **Environment Variables**
   - Edit **Path** variable
   - Add: `C:\xampp\php`
   - Click **OK** on all dialogs

4. **Verify**
   - Open new Command Prompt
   - Run: `composer --version`

### Plan C: Use Composer PHAR Directly

```bash
# Navigate to project directory
cd C:\xampp\htdocs\ClearPay

# Download composer.phar
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Use composer
php composer.phar install
```

---

## Step 5: Clone/Download Project

### Plan A: Clone from GitHub

```bash
# Open Command Prompt
cd C:\xampp\htdocs

# Clone repository
git clone https://github.com/yourusername/ClearPay.git

# Navigate to project
cd ClearPay
```

### Plan B: Download ZIP

1. **Download ZIP**
   - Go to GitHub repository
   - Click **Code** → **Download ZIP**
   - Extract to: `C:\xampp\htdocs\ClearPay`

2. **Verify Structure**
   - Should have: `app`, `public`, `vendor`, `writable` folders
   - Should have: `composer.json`, `spark` files

---

## Step 6: Install Dependencies

### Install PHP Dependencies

```bash
# Navigate to project directory
cd C:\xampp\htdocs\ClearPay

# Install dependencies
composer install
```

**Expected Output:**
- Creates `vendor` folder
- Downloads all required packages
- Takes 2-5 minutes

### Plan B: If Composer Install Fails

#### Problem: Memory limit exceeded

**Solution:**
```bash
# Increase PHP memory limit for this command
php -d memory_limit=512M composer.phar install
```

#### Problem: SSL certificate errors

**Solution:**
```bash
# Disable SSL verification (development only)
composer install --no-interaction --prefer-dist --ignore-platform-reqs
```

#### Problem: Timeout errors

**Solution:**
```bash
# Increase timeout
composer install --timeout=600
```

#### Problem: Missing extensions

**Solution:**
1. Check which extension is missing from error message
2. Enable it in `php.ini` (see Step 2)
3. Restart Apache
4. Run `composer install` again

---

## Step 7: Create .env File

### Create .env File

1. **Navigate to Project Root**
   - Location: `C:\xampp\htdocs\ClearPay`

2. **Create .env File**
   - Copy `env` file (if exists) to `.env`
   - Or create new file named `.env`

3. **Add Configuration**

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'http://localhost/ClearPay/public/'
# If using different port: app.baseURL = 'http://localhost:8080/ClearPay/public/'
# If using VirtualHost: app.baseURL = 'http://clearpay.local/'

app.appTimezone = 'Asia/Manila'
# Options: Asia/Manila, America/New_York, Europe/London, etc.

app.defaultLocale = 'en'

#--------------------------------------------------------------------
# SECURITY
#--------------------------------------------------------------------
# Generate encryption key: php spark key:generate
encryption.key = base64:YOUR_ENCRYPTION_KEY_HERE

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password = 
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_general_ci
database.default.strictOn = false
database.default.DBDebug = true

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.cookieName = 'ci_session'
session.expiration = 7200
session.savePath = 'C:\xampp\tmp'
session.matchIP = false
session.timeToUpdate = 300
session.regenerateDestroy = false

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------
logger.threshold = 4
# 0 = Emergency, 1 = Alert, 2 = Critical, 3 = Error, 4 = Warning, 5 = Notice, 6 = Info, 7 = Debug

#--------------------------------------------------------------------
# EMAIL (Optional - for password reset/verification)
#--------------------------------------------------------------------
# email.fromEmail = 'no-reply@example.com'
# email.fromName = 'ClearPay'
# email.protocol = 'smtp'
# email.SMTPHost = 'smtp.gmail.com'
# email.SMTPUser = 'your-email@gmail.com'
# email.SMTPPass = 'your-app-password'
# email.SMTPPort = 587
# email.SMTPCrypto = 'tls'
# email.SMTPTimeout = 5
# email.SMTPKeepAlive = false
```

### Generate Encryption Key

```bash
# Navigate to project directory
cd C:\xampp\htdocs\ClearPay

# Generate encryption key
php spark key:generate

# Copy the generated key
# Replace YOUR_ENCRYPTION_KEY_HERE in .env file
```

**Or manually generate:**
```bash
php -r "echo base64_encode(random_bytes(32));"
```

### Plan B: If .env Doesn't Work

#### Problem: Configuration not loading

**Solution 1: Check File Permissions**
- Ensure `.env` file is readable
- Check file isn't locked by another program

**Solution 2: Verify File Location**
- `.env` must be in project root: `C:\xampp\htdocs\ClearPay\.env`
- Not in `app` or `public` folder

**Solution 3: Check File Encoding**
- Save as UTF-8 without BOM
- Use Notepad++ or VS Code
- Avoid Windows Notepad

**Solution 4: Use Config Files Directly**
- Edit `app/Config/App.php` directly
- Edit `app/Config/Database.php` directly
- Not recommended for production

---

## Step 8: Database Setup

### Create Database

1. **Open phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`
   - Or: `http://localhost:8080/phpmyadmin` (if using port 8080)

2. **Create Database**
   - Click **New** in left sidebar
   - Database name: `clearpaydb`
   - Collation: `utf8mb4_general_ci`
   - Click **Create**

### Plan B: If phpMyAdmin Doesn't Work

#### Problem: Can't access phpMyAdmin

**Solution 1: Use Command Line**
```bash
# Open Command Prompt
cd C:\xampp\mysql\bin

# Connect to MySQL
mysql.exe -u root

# Create database
CREATE DATABASE clearpaydb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

# Exit
exit;
```

**Solution 2: Check MySQL is Running**
- Verify MySQL is running in XAMPP Control Panel
- Check port 3306 is not blocked
- Check firewall settings

**Solution 3: Reset MySQL Root Password**
```bash
# Stop MySQL in XAMPP
# Open Command Prompt as Administrator
cd C:\xampp\mysql\bin

# Start MySQL in safe mode
mysqld.exe --skip-grant-tables

# Open new Command Prompt
mysql.exe -u root

# Reset password
USE mysql;
UPDATE user SET password=PASSWORD('') WHERE User='root';
FLUSH PRIVILEGES;
exit;

# Restart MySQL normally in XAMPP
```

---

## Step 9: Run Migrations

### Run Database Migrations

```bash
# Navigate to project directory
cd C:\xampp\htdocs\ClearPay

# Run migrations
php spark migrate

# Expected output:
# Running all new migrations...
# Migrations complete.
```

### Seed Database (Optional)

```bash
# Run seeders
php spark db:seed

# Or run specific seeder
php spark db:seed UserSeeder
```

### Plan B: If Migrations Fail

#### Problem: Migration errors

**Solution 1: Check Database Connection**
```bash
# Test database connection
php spark db:table users

# Should show table structure or connection error
```

**Solution 2: Check .env Database Settings**
- Verify database name: `clearpaydb`
- Verify username: `root`
- Verify password: (empty or correct)
- Verify hostname: `localhost`

**Solution 3: Reset Migrations**
```sql
-- In phpMyAdmin, run:
TRUNCATE TABLE migrations;
```

Then run `php spark migrate` again.

**Solution 4: Check Migration Files**
- Verify migration files exist in `app/Database/Migrations/`
- Should have 15 migration files
- Check file permissions

**Solution 5: Manual Migration**
```bash
# Rollback all migrations
php spark migrate:rollback

# Run migrations again
php spark migrate
```

---

## Step 10: Verify Installation

### 1. Check File Permissions

**Ensure writable directories exist and are writable:**

```bash
# Check these directories exist:
C:\xampp\htdocs\ClearPay\writable\cache
C:\xampp\htdocs\ClearPay\writable\logs
C:\xampp\htdocs\ClearPay\writable\session
C:\xampp\htdocs\ClearPay\public\uploads\profile
C:\xampp\htdocs\ClearPay\public\uploads\payment_proofs
C:\xampp\htdocs\ClearPay\public\uploads\qr_receipts
```

**If missing, create them:**
```bash
# Create directories
mkdir writable\cache
mkdir writable\logs
mkdir writable\session
mkdir public\uploads\profile
mkdir public\uploads\payment_proofs
mkdir public\uploads\qr_receipts
```

### 2. Access Application

1. **Open Browser**
   - URL: `http://localhost/ClearPay/public/`
   - Or: `http://localhost:8080/ClearPay/public/` (if using port 8080)

2. **Expected Result**
   - Should see login page
   - No error messages
   - Page loads correctly

### 3. Test Login

**Default Admin Credentials:**
- Username: `admin`
- Password: `admin123`

**If default user doesn't exist, create it:**
```bash
# Run user seeder
php spark db:seed UserSeeder
```

### 4. Verify Features

- ✅ Login works
- ✅ Dashboard loads
- ✅ Database connection works
- ✅ File uploads work (test profile picture)
- ✅ No PHP errors in logs

### Plan B: If Application Doesn't Load

#### Problem: 404 Error

**Solution 1: Check Base URL**
- Verify `.env` has correct `app.baseURL`
- Should match your folder structure
- Example: `http://localhost/ClearPay/public/`

**Solution 2: Check Apache Configuration**
- Verify `mod_rewrite` is enabled
- Verify `AllowOverride All` is set
- Check `.htaccess` file exists in `public` folder

**Solution 3: Access via Direct Path**
- Try: `http://localhost/ClearPay/public/index.php`
- If this works, mod_rewrite issue
- If this doesn't work, PHP configuration issue

#### Problem: White Screen / Blank Page

**Solution 1: Check PHP Errors**
- Enable error display in `php.ini`: `display_errors = On`
- Restart Apache
- Reload page
- Check for error messages

**Solution 2: Check Error Logs**
- Location: `C:\xampp\htdocs\ClearPay\writable\logs\log-YYYY-MM-DD.log`
- Open latest log file
- Look for errors

**Solution 3: Check PHP Version**
```bash
# Check PHP version
php -v

# Should be 8.1 or higher
```

#### Problem: Database Connection Error

**Solution 1: Verify Database Exists**
```sql
-- In phpMyAdmin
SHOW DATABASES;
-- Should see 'clearpaydb'
```

**Solution 2: Test Connection**
```bash
# Test database connection
php spark db:table users
```

**Solution 3: Check .env Database Settings**
- Verify all database settings in `.env`
- Check for typos
- Ensure no extra spaces

#### Problem: Permission Denied Errors

**Solution 1: Check Folder Permissions**
- Right-click `writable` folder
- Properties → Security
- Ensure "Users" have "Modify" permission

**Solution 2: Run as Administrator**
- Run XAMPP Control Panel as Administrator
- Restart Apache

**Solution 3: Check Windows Firewall**
- Add exception for Apache
- Add exception for MySQL

---

## Troubleshooting & Plan B's

### Common Issues Summary

| Problem | Solution |
|---------|----------|
| Apache won't start | Change port 80 to 8080 in httpd.conf |
| MySQL won't start | Change port 3306 to 3307 in my.ini |
| Extensions not loading | Enable in php.ini and restart Apache |
| 404 errors | Enable mod_rewrite and AllowOverride |
| Database connection failed | Check .env database settings |
| White screen | Enable error display, check logs |
| File upload fails | Increase upload_max_filesize in php.ini |
| Composer install fails | Check PHP extensions, increase memory |

### Quick Diagnostic Commands

```bash
# Check PHP version
php -v

# Check PHP extensions
php -m

# Check Composer
composer --version

# Test database connection
php spark db:table users

# Check routes
php spark routes

# Clear cache
php spark cache:clear

# Check configuration
php spark env
```

### Emergency Reset

**If everything fails, reset to clean state:**

1. **Backup Database**
   ```sql
   -- Export database in phpMyAdmin
   ```

2. **Delete Project**
   ```bash
   # Delete project folder
   rm -rf C:\xampp\htdocs\ClearPay
   ```

3. **Start Fresh**
   - Follow installation guide from Step 5
   - Import database backup
   - Run migrations

---

## Post-Installation Checklist

- [ ] XAMPP Apache is running
- [ ] XAMPP MySQL is running
- [ ] All PHP extensions are enabled
- [ ] php.ini configured correctly
- [ ] httpd.conf configured correctly
- [ ] Composer installed and working
- [ ] Dependencies installed (`vendor` folder exists)
- [ ] `.env` file created and configured
- [ ] Database `clearpaydb` created
- [ ] Migrations run successfully
- [ ] Application accessible in browser
- [ ] Login works with default credentials
- [ ] File uploads work
- [ ] No errors in logs

---

## Support

If you encounter issues not covered in this guide:

1. **Check Logs**
   - Application: `writable/logs/`
   - Apache: `C:\xampp\apache\logs\error.log`
   - PHP: Check `php.ini` error log location

2. **Verify Configuration**
   - Run: `php spark env` to check environment
   - Run: `php spark routes` to check routes
   - Check database connection: `php spark db:table users`

3. **Common Resources**
   - CodeIgniter 4 Documentation: https://codeigniter.com/user_guide/
   - XAMPP Documentation: https://www.apachefriends.org/docs/
   - PHP Manual: https://www.php.net/manual/

---

## Additional Configuration (Optional)

### Virtual Host Setup (Cleaner URLs)

**Instead of:** `http://localhost/ClearPay/public/`  
**Use:** `http://clearpay.local/`

1. **Edit Hosts File**
   - Location: `C:\Windows\System32\drivers\etc\hosts`
   - Add: `127.0.0.1 clearpay.local`
   - Save (requires Administrator)

2. **Edit httpd-vhosts.conf**
   - Location: `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
   - Add:
   ```apache
   <VirtualHost *:80>
       ServerName clearpay.local
       DocumentRoot "C:/xampp/htdocs/ClearPay/public"
       <Directory "C:/xampp/htdocs/ClearPay/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

3. **Enable Virtual Hosts**
   - In `httpd.conf`, uncomment:
   ```apache
   Include conf/extra/httpd-vhosts.conf
   ```

4. **Update .env**
   ```env
   app.baseURL = 'http://clearpay.local/'
   ```

5. **Restart Apache**
   - Access: `http://clearpay.local/`

---

**Installation Complete! 🎉**

You should now have ClearPay running on your local machine. If you encounter any issues, refer to the troubleshooting section or check the logs.

