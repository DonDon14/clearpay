# ✅ Hostinger Deployment Checklist

Use this checklist to ensure a smooth deployment of ClearPay to Hostinger.

---

## 📋 Pre-Deployment

### Local Preparation
- [ ] Code is tested and working locally
- [ ] All sensitive data removed from code
- [ ] `.env` file is NOT included in upload
- [ ] Unnecessary files removed (tests, temp files, etc.)
- [ ] Database backup created (if migrating existing data)
- [ ] All dependencies listed in `composer.json`

---

## 🌐 Hostinger Account Setup

### Account & Domain
- [ ] Hostinger account is active
- [ ] Domain is connected to Hostinger
- [ ] DNS settings are correct
- [ ] hPanel access is working

### PHP Configuration
- [ ] PHP version set to 8.1 or higher
- [ ] Required PHP extensions enabled:
  - [ ] mysqli
  - [ ] mbstring
  - [ ] openssl
  - [ ] curl
  - [ ] zip
  - [ ] gd
  - [ ] fileinfo

---

## 📤 File Upload

### Upload Method
- [ ] Files uploaded via File Manager OR
- [ ] Files uploaded via FTP/SFTP
- [ ] All files uploaded successfully
- [ ] Directory structure maintained correctly

### File Structure Verification
- [ ] `public/` folder exists (or files in root if flat structure)
- [ ] `app/` folder exists
- [ ] `writable/` folder exists
- [ ] `composer.json` exists
- [ ] `.htaccess` file exists in `public/` or root

---

## 🗄️ Database Setup

### Database Creation
- [ ] Database created in hPanel
- [ ] Database user created
- [ ] User assigned to database with ALL PRIVILEGES
- [ ] Database credentials noted down securely

### Database Import/Migration
- [ ] Database imported via phpMyAdmin OR
- [ ] Migrations run via SSH: `php spark migrate`
- [ ] Seeders run: `php spark db:seed` (if needed)
- [ ] Database connection tested

---

## ⚙️ Configuration

### Environment File (.env)
- [ ] `.env` file created on server
- [ ] `CI_ENVIRONMENT = production` set
- [ ] `app.baseURL` updated with actual domain (HTTPS)
- [ ] `app.appTimezone` set correctly
- [ ] Encryption key generated and added
- [ ] Database credentials configured:
  - [ ] hostname (usually `localhost`)
  - [ ] database name
  - [ ] username
  - [ ] password
  - [ ] DBDebug set to `false`
- [ ] Email settings configured (if using email features)

### Document Root
- [ ] Document root set to `public/` folder OR
- [ ] Flat structure configured correctly

---

## 📦 Dependencies

### Composer Dependencies
- [ ] Dependencies installed via SSH: `composer install --no-dev` OR
- [ ] `vendor/` folder uploaded manually
- [ ] All dependencies installed successfully
- [ ] No missing dependency errors

---

## 🔐 File Permissions

### Permissions Set
- [ ] `writable/` folder: **775** (recursive)
- [ ] Other folders: **755**
- [ ] Files: **644**
- [ ] `spark` file: **755** (executable)

### Permission Verification
- [ ] Can write to `writable/cache/`
- [ ] Can write to `writable/logs/`
- [ ] Can write to `writable/session/`
- [ ] Can write to `writable/uploads/`

---

## 🔒 SSL Certificate

### SSL Setup
- [ ] SSL certificate installed via hPanel
- [ ] SSL certificate active (green padlock)
- [ ] HTTPS redirect enabled in `.htaccess`
- [ ] Site accessible via HTTPS

---

## ✅ Testing

### Basic Functionality
- [ ] Homepage loads without errors
- [ ] No PHP errors displayed
- [ ] CSS files load correctly
- [ ] JavaScript files load correctly
- [ ] Images display correctly

### Application Features
- [ ] Database connection works
- [ ] Login functionality works
- [ ] Registration works (if applicable)
- [ ] File uploads work
- [ ] Email sending works (test password reset)
- [ ] All routes accessible
- [ ] Session management works

### Security Checks
- [ ] HTTPS enforced (HTTP redirects to HTTPS)
- [ ] No sensitive information exposed
- [ ] Error messages don't reveal system info
- [ ] `.env` file not accessible via browser

---

## 📊 Post-Deployment

### Monitoring
- [ ] Error logs checked (no critical errors)
- [ ] Application performance acceptable
- [ ] Database queries optimized
- [ ] No memory/timeout issues

### Backup Setup
- [ ] Backup strategy configured
- [ ] Regular backups scheduled
- [ ] Backup restoration tested

### Documentation
- [ ] Deployment notes documented
- [ ] Credentials stored securely
- [ ] Team members informed
- [ ] Access credentials shared (if needed)

---

## 🐛 Troubleshooting (if issues found)

### Common Issues
- [ ] 500 errors → Check `.htaccess` and PHP version
- [ ] Database errors → Verify `.env` database settings
- [ ] Permission errors → Fix file permissions
- [ ] Missing files → Re-upload missing files
- [ ] SSL issues → Verify SSL certificate installation

---

## 📝 Notes

**Date Deployed:** _______________

**Deployed By:** _______________

**Domain:** _______________

**Database Name:** _______________

**Special Notes:**
_________________________________
_________________________________
_________________________________

---

## 🎉 Deployment Complete!

Once all items are checked, your ClearPay application should be live on Hostinger!

**Next Steps:**
1. Monitor the application for the first 24-48 hours
2. Set up regular backups
3. Keep dependencies updated
4. Monitor error logs regularly

---

**Last Updated:** 2024

