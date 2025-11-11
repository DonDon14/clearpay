# ClearPay Production Deployment Guide
## Domain: clearpay.fwh.is

This guide provides step-by-step instructions for deploying the ClearPay web application to your production server.

---

## 📋 Pre-Deployment Checklist

### Server Requirements
- **PHP**: Version 8.1 or higher
- **MySQL/MariaDB**: Version 5.7+ or 10.2+
- **Apache**: With mod_rewrite enabled
- **SSL Certificate**: Required for HTTPS (clearpay.fwh.is)
- **Composer**: Installed on the server

### Required PHP Extensions
- `mysqli` or `pdo_mysql`
- `mbstring`
- `openssl`
- `curl`
- `gd` or `imagick` (for image processing)
- `zip` (for backups)

---

## 📁 Step 1: Prepare Files for Upload

### Files to Upload
Upload the following directories and files to your server:

```
ClearPay/
├── app/                    # Application code
├── public/                 # Public web root (this should be your document root)
│   ├── .htaccess
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── uploads/
├── vendor/                 # Composer dependencies (or install on server)
├── writable/               # Writable directories
│   ├── cache/
│   ├── logs/
│   ├── session/
│   ├── temp/
│   └── uploads/
├── spark                   # CodeIgniter CLI tool
├── composer.json
├── composer.lock
└── .env                    # Environment configuration (create on server)
```

### Files to EXCLUDE (Do NOT upload)
- `flutter_app/` - Flutter mobile app (separate deployment)
- `tests/` - Test files
- `writable/cache/*` - Cache files (keep directory structure)
- `writable/logs/*` - Log files (keep directory structure)
- `writable/session/*` - Session files (keep directory structure)
- `writable/temp/*` - Temp files (keep directory structure)
- `.git/` - Git repository
- `.idea/`, `.vscode/` - IDE files
- `*.md` files (optional, documentation only)

---

## 🗄️ Step 2: Database Setup

### 2.1 Create Database
1. Log into your hosting control panel (cPanel, Plesk, etc.)
2. Create a new MySQL database (e.g., `clearpaydb` or `yourusername_clearpay`)
3. Create a database user with full privileges
4. Note down:
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### 2.2 Import Database Structure
**Option A: Using Migrations (Recommended)**
```bash
# SSH into your server
cd /path/to/clearpay
php spark migrate
php spark db:seed DatabaseSeeder
```

**Option B: Using phpMyAdmin**
1. Export your local database
2. Import via phpMyAdmin on your hosting server

---

## ⚙️ Step 3: Server Configuration

### 3.1 Document Root Setup
**IMPORTANT**: Your web server's document root should point to the `public/` directory.

**For cPanel:**
- Upload files to `public_html/` (which should be your `public/` folder)
- Or create a subdomain and point it to `public/` directory

**For Direct Server Access:**
- Configure Apache VirtualHost to point to `/path/to/ClearPay/public/`
- Example VirtualHost configuration:
```apache
<VirtualHost *:443>
    ServerName clearpay.fwh.is
    DocumentRoot /path/to/ClearPay/public
    
    SSLEngine on
    SSLCertificateFile /path/to/ssl/certificate.crt
    SSLCertificateKeyFile /path/to/ssl/private.key
    
    <Directory /path/to/ClearPay/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3.2 File Permissions
Set proper file permissions (via SSH or FTP):
```bash
# Navigate to ClearPay directory
cd /path/to/ClearPay

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make writable directories writable
chmod -R 775 writable/
chown -R www-data:www-data writable/  # Adjust user:group as needed

# Make spark executable
chmod +x spark
```

**For cPanel/Shared Hosting:**
- Use File Manager to set permissions:
  - Folders: `755`
  - Files: `644`
  - `writable/` folder: `775` (recursive)

---

## 🔧 Step 4: Environment Configuration

### 4.1 Create .env File
Create a `.env` file in the root directory (same level as `composer.json`):

```env
# Environment
CI_ENVIRONMENT = production

# Base URL (IMPORTANT: Update this!)
app.baseURL = 'https://clearpay.fwh.is/'
app.appTimezone = 'Asia/Manila'

# Security - Generate a new encryption key
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

# Email Configuration (Update with your SMTP settings)
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

### 4.2 Generate Encryption Key
```bash
php spark key:generate
```
Copy the generated key and paste it into `.env` file under `encryption.key`.

---

## 📦 Step 5: Install Dependencies

### 5.1 Install Composer Dependencies
If you didn't upload the `vendor/` folder, install dependencies on the server:

```bash
cd /path/to/ClearPay
composer install --no-dev --optimize-autoloader
```

**For Shared Hosting:**
- Install dependencies locally
- Upload the `vendor/` folder to your server

---

## ✅ Step 6: Verify Configuration

### 6.1 Check Configuration Files
The following files have been pre-configured for production:

✅ **`app/Config/App.php`**
- `baseURL` = `'https://clearpay.fwh.is/'`
- `forceGlobalSecureRequests` = `true`

✅ **`app/Config/Cors.php`**
- Production domain added to allowed origins

✅ **`public/.htaccess`**
- HTTPS redirect enabled

### 6.2 Test Database Connection
```bash
php spark db:table
```
If this runs without errors, your database connection is working.

---

## 🧪 Step 7: Testing Checklist

After deployment, test the following:

### 7.1 Basic Functionality
- [ ] Access `https://clearpay.fwh.is/` - Should redirect to login
- [ ] Login with admin credentials
- [ ] Check dashboard loads correctly
- [ ] Verify HTTPS is working (green padlock in browser)

### 7.2 File Uploads
- [ ] Upload profile picture
- [ ] Upload payment proof
- [ ] Verify files are saved in `writable/uploads/`

### 7.3 Database Operations
- [ ] Create a new payer
- [ ] Create a contribution
- [ ] Process a payment
- [ ] Check logs for errors

### 7.4 Email Functionality
- [ ] Test password reset email
- [ ] Test verification email
- [ ] Check email settings in admin panel

### 7.5 API Endpoints (for Flutter app)
- [ ] Test: `https://clearpay.fwh.is/api/payer/login`
- [ ] Verify CORS headers are present
- [ ] Test authentication token

---

## 🔒 Step 8: Security Hardening

### 8.1 Protect .env File
Ensure `.env` file is not accessible via web:
- Already protected by `.htaccess` in most setups
- Verify: Try accessing `https://clearpay.fwh.is/.env` - should return 403 or 404

### 8.2 Protect Writable Directory
Ensure `writable/` is not accessible via web:
- Should be outside document root, OR
- Protected by `.htaccess` with `Deny from all`

### 8.3 Update Default Credentials
- [ ] Change default admin password
- [ ] Review user accounts
- [ ] Enable 2FA if available

### 8.4 Regular Backups
Set up automated backups for:
- Database (daily)
- `writable/uploads/` (daily)
- Configuration files (weekly)

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error
**Solutions:**
1. Check `writable/logs/` for error messages
2. Verify file permissions on `writable/` directory
3. Check PHP error logs in hosting control panel
4. Verify `.env` file exists and is configured correctly
5. Check Apache error logs

### Issue: Database Connection Failed
**Solutions:**
1. Verify database credentials in `.env`
2. Check database host (may not be `localhost` on shared hosting)
3. Verify database user has proper permissions
4. Check if database server is running

### Issue: CSS/JS Not Loading
**Solutions:**
1. Clear browser cache
2. Verify `baseURL` in `app/Config/App.php` is correct
3. Check file permissions on `public/css/` and `public/js/`
4. Verify `.htaccess` is working

### Issue: File Uploads Not Working
**Solutions:**
1. Check `writable/uploads/` permissions (should be 775)
2. Verify PHP `upload_max_filesize` and `post_max_size` settings
3. Check disk space on server
4. Review error logs

### Issue: HTTPS Redirect Loop
**Solutions:**
1. Check if server is behind a proxy (update `proxyIPs` in `App.php`)
2. Verify SSL certificate is properly installed
3. Check `.htaccess` redirect rules
4. Temporarily disable `forceGlobalSecureRequests` to test

### Issue: CORS Errors (Flutter App)
**Solutions:**
1. Verify `clearpay.fwh.is` is in `allowedOrigins` in `Cors.php`
2. Check if API endpoints are accessible
3. Verify OPTIONS requests are handled
4. Check server logs for CORS-related errors

---

## 📞 Support Information

### Default Admin Credentials
**⚠️ CHANGE THESE AFTER FIRST LOGIN!**
- Username: `admin`
- Password: `admin123`

### Important Files Location
- Configuration: `app/Config/`
- Logs: `writable/logs/`
- Uploads: `writable/uploads/`
- Cache: `writable/cache/`

### Useful Commands
```bash
# Clear cache
php spark cache:clear

# Run migrations
php spark migrate

# Run seeders
php spark db:seed DatabaseSeeder

# Check system info
php spark
```

---

## 📝 Post-Deployment Tasks

1. **Update Flutter App API URL**
   - Update `baseUrl` in Flutter app to `https://clearpay.fwh.is`
   - Rebuild and redeploy Flutter app

2. **Monitor Logs**
   - Check `writable/logs/` regularly
   - Set up log rotation if needed

3. **Performance Optimization**
   - Enable OPcache in PHP
   - Configure caching if needed
   - Optimize database queries

4. **SSL Certificate**
   - Ensure SSL certificate is valid
   - Set up auto-renewal if using Let's Encrypt

5. **Backup Strategy**
   - Set up automated database backups
   - Backup uploads directory regularly
   - Test restore procedures

---

## ✅ Deployment Complete!

Your ClearPay application should now be live at:
**https://clearpay.fwh.is**

If you encounter any issues, refer to the troubleshooting section or check the error logs in `writable/logs/`.

---

## 📋 Quick Reference

| Item | Value |
|------|-------|
| Domain | clearpay.fwh.is |
| Base URL | https://clearpay.fwh.is/ |
| Document Root | `/path/to/ClearPay/public/` |
| Environment | production |
| PHP Version | 8.1+ |
| Database | MySQL/MariaDB |

---

**Last Updated:** $(date)
**Version:** 1.0

