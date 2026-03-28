# InfinityFree Hosting Deployment Guide
## For clearpay.fwh.is on InfinityFree

This guide is specifically tailored for deploying ClearPay to InfinityFree hosting.

---

## ⚠️ Important InfinityFree Limitations

Before proceeding, be aware of these InfinityFree limitations:

1. **Security System**: InfinityFree has a JavaScript/cookie-based security system that may block:
   - API requests from mobile apps
   - Automated scripts
   - Some CORS requests

2. **Resource Limits**:
   - 5 GB disk space
   - 50,000 hits per day
   - CPU/Memory limits (can cause suspension)

3. **No Email Accounts**: Free plan doesn't include email (but SMTP can work)

4. **PHP Version**: Check available PHP version (may need PHP 8.1+)

---

## 📁 Folder Structure for InfinityFree

InfinityFree uses `htdocs/` as the document root. You have **TWO options**:

### Option 1: Standard Structure (Recommended)
Upload everything to `htdocs/` and point document root to `public/`:

```
htdocs/
├── app/
├── public/          ← Document root should point here
│   ├── .htaccess
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── uploads/
├── vendor/
├── writable/
├── spark
├── composer.json
└── .env
```

### Option 2: Flat Structure (If Option 1 doesn't work)
If InfinityFree doesn't allow changing document root, use this structure:

```
htdocs/
├── .htaccess       ← Move from public/
├── index.php       ← Move from public/
├── css/            ← Move from public/css/
├── js/             ← Move from public/js/
├── uploads/        ← Move from public/uploads/
├── app/
├── vendor/
├── writable/
├── spark
├── composer.json
└── .env
```

**⚠️ If using Option 2, you'll need to update paths in `public/index.php`**

---

## 🔧 Step-by-Step Deployment

### Step 1: Check InfinityFree Requirements

1. **PHP Version**: 
   - Log into InfinityFree control panel
   - Check available PHP versions
   - Ensure PHP 8.1+ is available
   - Set PHP version in control panel

2. **MySQL Database**:
   - Create database via InfinityFree control panel
   - Note: Database host is usually `sqlXXX.infinityfree.com` (NOT `localhost`)
   - Note down: database name, username, password, host

3. **SSL Certificate**:
   - InfinityFree provides free SSL via Let's Encrypt
   - Enable SSL in control panel

---

### Step 2: Upload Files

**For Option 1 (Standard Structure):**

1. Upload entire project to `htdocs/`
2. In InfinityFree control panel, set document root to `htdocs/public/`
   - Or use subdomain and point it to `public/` folder

**For Option 2 (Flat Structure):**

1. Upload files with this structure:
   ```
   htdocs/
   ├── .htaccess (from public/.htaccess)
   ├── index.php (from public/index.php)
   ├── css/ (from public/css/)
   ├── js/ (from public/js/)
   ├── uploads/ (from public/uploads/)
   ├── app/
   ├── vendor/
   ├── writable/
   ├── spark
   ├── composer.json
   └── .env
   ```

2. **Update `index.php`** (if using Option 2):
   ```php
   // Change this line:
   require FCPATH . '../app/Config/Paths.php';
   // To:
   require FCPATH . 'app/Config/Paths.php';
   ```

---

### Step 3: Update Configuration

#### 3.1 Create `.env` File

Create `.env` in `htdocs/` (root level):

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://clearpay.fwh.is/'
app.appTimezone = 'Asia/Manila'

# Generate encryption key: php spark key:generate
encryption.key = base64:YOUR_GENERATED_KEY_HERE

# Database (InfinityFree uses remote MySQL host)
database.default.hostname = sqlXXX.infinityfree.com
database.default.database = epiz_XXXXXX_clearpay
database.default.username = epiz_XXXXXX_dbuser
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.DBDebug = false
database.default.port = 3306

# Email (use external SMTP like Gmail)
email.fromEmail = 'project.clearpay@gmail.com'
email.fromName = 'ClearPay'
email.protocol = 'smtp'
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = 'project.clearpay@gmail.com'
email.SMTPPass = 'your_app_password'
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
```

**⚠️ Important**: InfinityFree database host is NOT `localhost` - it's usually `sqlXXX.infinityfree.com`

#### 3.2 Update `.htaccess` (if needed)

The current `.htaccess` should work, but verify:

```apache
# If document root is public/, RewriteBase should be:
RewriteBase /

# If using flat structure, RewriteBase should be:
RewriteBase /
```

---

### Step 4: Set File Permissions

Via InfinityFree File Manager or FTP:

1. **Directories**: `755`
2. **Files**: `644`
3. **writable/**: `775` (recursive)
   - This is critical for logs, cache, sessions, uploads

---

### Step 5: Install Dependencies

**Option A: Via SSH (if available)**
```bash
cd htdocs
composer install --no-dev --optimize-autoloader
```

**Option B: Upload vendor folder**
- Install dependencies locally
- Upload entire `vendor/` folder to server

---

### Step 6: Database Setup

**Via SSH (if available):**
```bash
cd htdocs
php spark migrate
php spark db:seed DatabaseSeeder
```

**Via phpMyAdmin:**
1. Access phpMyAdmin from InfinityFree control panel
2. Import your local database dump
3. Or manually run SQL migrations

---

### Step 7: Generate Encryption Key

**Via SSH:**
```bash
php spark key:generate
```

**Manually:**
- Generate a random 32-character base64 key
- Add to `.env`: `encryption.key = base64:YOUR_KEY_HERE`

---

## ⚠️ InfinityFree-Specific Issues & Solutions

### Issue 1: Security System Blocking API Requests

**Problem**: InfinityFree's security system may block API requests from Flutter app.

**Solutions:**
1. **Whitelist IP addresses** (if possible in control panel)
2. **Add security bypass** (not recommended, but may be needed):
   - Contact InfinityFree support
   - Or upgrade to premium plan

3. **Use different hosting** for API endpoints if issues persist

### Issue 2: Database Connection Failed

**Problem**: Using `localhost` as database host won't work.

**Solution**: Use the database host provided by InfinityFree:
- Usually: `sqlXXX.infinityfree.com`
- Check in InfinityFree control panel → MySQL Databases

### Issue 3: File Uploads Not Working

**Problem**: `writable/` folder permissions or disk space.

**Solutions:**
1. Check `writable/uploads/` permissions (should be `775`)
2. Check disk space (5 GB limit)
3. Verify PHP `upload_max_filesize` settings

### Issue 4: Composer Not Available

**Problem**: InfinityFree may not have Composer installed.

**Solution**: 
- Install dependencies locally
- Upload `vendor/` folder to server

### Issue 5: PHP Version Issues

**Problem**: PHP 8.1+ may not be available.

**Solution**:
- Check available PHP versions in control panel
- Set to highest available version
- Update `public/index.php` minimum version if needed

---

## 🧪 Testing Checklist

After deployment:

- [ ] Site accessible at `https://clearpay.fwh.is/`
- [ ] HTTPS working (SSL certificate active)
- [ ] Login page loads
- [ ] Can log in with admin credentials
- [ ] Dashboard loads
- [ ] Database operations work
- [ ] File uploads work
- [ ] API endpoints accessible (test from browser)
- [ ] CORS working (test from Flutter app)

---

## 🔒 Security Considerations

1. **Protect `.env` file**:
   - Ensure `.htaccess` blocks access
   - Verify: `https://clearpay.fwh.is/.env` should return 403/404

2. **Protect `writable/` directory**:
   - Should not be web-accessible
   - Or protected by `.htaccess`

3. **Change default passwords**:
   - Admin password
   - Database password (if possible)

---

## 📝 Alternative: If InfinityFree Doesn't Work

If you encounter too many issues with InfinityFree (especially with API/CORS), consider:

1. **Upgrade to InfinityFree Premium** (removes some limitations)
2. **Use different hosting**:
   - 000webhost (free, similar structure)
   - AwardSpace (free)
   - Paid hosting (more reliable for production)

---

## ✅ Quick Reference

| Item | Value |
|------|-------|
| Document Root | `htdocs/public/` (Option 1) or `htdocs/` (Option 2) |
| Database Host | `sqlXXX.infinityfree.com` (NOT localhost) |
| PHP Version | 8.1+ (check in control panel) |
| SSL | Free via Let's Encrypt |
| Disk Space | 5 GB limit |
| Daily Hits | 50,000 limit |

---

## 🆘 Getting Help

1. **InfinityFree Forums**: [forum.infinityfree.com](https://forum.infinityfree.com)
2. **InfinityFree Knowledge Base**: Check their documentation
3. **Check Logs**: `writable/logs/` for application errors

---

**Note**: InfinityFree's free plan has limitations that may affect API functionality. For production use with mobile apps, consider a paid hosting plan for better reliability and fewer restrictions.

