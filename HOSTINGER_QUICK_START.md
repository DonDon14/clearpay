# 🚀 Hostinger Quick Start Guide

Quick reference for deploying ClearPay to Hostinger. For detailed instructions, see [HOSTINGER_DEPLOYMENT_GUIDE.md](HOSTINGER_DEPLOYMENT_GUIDE.md).

---

## ⚡ Quick Steps

### 1. Prepare Files Locally
```bash
# Remove unnecessary files
# Ensure .env is NOT included
# Test application locally
```

### 2. Upload to Hostinger
- Use **File Manager** or **FTP**
- Upload entire project to `public_html/`
- Set document root to `public_html/public/`

### 3. Create Database
- Go to **Databases** → **MySQL Databases**
- Create database and user
- Assign user to database with ALL PRIVILEGES

### 4. Configure Environment
Create `.env` file in project root:

```env
CI_ENVIRONMENT = production
app.baseURL = 'https://yourdomain.com/'
app.appTimezone = 'Asia/Manila'

# Generate key: php spark key:generate
encryption.key = base64:YOUR_KEY_HERE

# Database
database.default.hostname = localhost
database.default.database = your_db_name
database.default.username = your_db_user
database.default.password = your_db_password
database.default.DBDriver = MySQLi
database.default.DBDebug = false

# Email (optional)
email.fromEmail = 'project.clearpay@gmail.com'
email.fromName = 'ClearPay'
email.protocol = 'smtp'
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = 'project.clearpay@gmail.com'
email.SMTPPass = 'your_app_password'
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
```

### 5. Install Dependencies
**Via SSH:**
```bash
cd public_html
composer install --no-dev --optimize-autoloader
php spark key:generate
php spark migrate
```

**Without SSH:**
- Install Composer locally
- Upload `vendor/` folder
- Generate key locally and add to `.env`

### 6. Set Permissions
```bash
chmod -R 775 writable/
chmod +x spark
```

### 7. Enable SSL
- Go to **Advanced** → **SSL**
- Install free SSL certificate
- HTTPS redirect is already enabled in `.htaccess`

### 8. Test
- Visit `https://yourdomain.com/`
- Test login, database, uploads

---

## 🔧 Common Commands

### Generate Encryption Key
```bash
php spark key:generate
```

### Run Migrations
```bash
php spark migrate
php spark db:seed
```

### Check PHP Version
```bash
php -v
```

### Install Composer Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

---

## ⚠️ Important Notes

1. **Document Root:** Set to `public/` folder (Hostinger allows this)
2. **PHP Version:** Use PHP 8.1 or higher
3. **Database Host:** Usually `localhost` on Hostinger
4. **SSL:** Install SSL before enabling HTTPS redirect
5. **Permissions:** `writable/` folder must be **775**

---

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 Error | Check `.htaccess`, PHP version, permissions |
| Database Error | Verify `.env` database credentials |
| CSS/JS Not Loading | Check base URL, file paths |
| Upload Not Working | Set `writable/uploads/` to 775 |
| SSL Not Working | Install SSL certificate in hPanel |

---

## 📞 Need Help?

- **Full Guide:** [HOSTINGER_DEPLOYMENT_GUIDE.md](HOSTINGER_DEPLOYMENT_GUIDE.md)
- **Checklist:** [HOSTINGER_DEPLOYMENT_CHECKLIST.md](HOSTINGER_DEPLOYMENT_CHECKLIST.md)
- **Hostinger Support:** Available 24/7 in hPanel

---

**Last Updated:** 2024

