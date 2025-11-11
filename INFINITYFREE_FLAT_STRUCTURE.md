# InfinityFree Flat Structure Deployment Guide
## When Document Root Cannot Be Changed

Since InfinityFree doesn't allow changing the document root, we need to use a **flat structure** where everything is in `htdocs/`.

---

## 📁 Required Folder Structure

Upload files with this structure (document root = `htdocs/`):

```
htdocs/
├── .htaccess          ← From public/.htaccess
├── index.php          ← From public/index.php (needs path updates)
├── favicon.ico        ← From public/favicon.ico
├── robots.txt         ← From public/robots.txt
├── css/               ← From public/css/
├── js/                ← From public/js/
├── uploads/           ← From public/uploads/
├── app/
├── vendor/
├── writable/
├── spark
├── composer.json
├── composer.lock
└── .env               ← Create on server
```

---

## 🔧 Step-by-Step Instructions

### Step 1: Prepare Files Locally

1. **Create a deployment folder** on your computer
2. **Copy the following structure:**

```
deployment/
├── .htaccess          (copy from public/.htaccess)
├── index.php          (copy from public/index.php - will modify)
├── favicon.ico        (copy from public/favicon.ico)
├── robots.txt         (copy from public/robots.txt)
├── css/               (copy entire public/css/ folder)
├── js/                (copy entire public/js/ folder)
├── uploads/           (copy entire public/uploads/ folder)
├── app/               (copy entire app/ folder)
├── vendor/            (copy entire vendor/ folder OR install on server)
├── writable/          (copy entire writable/ folder)
├── spark              (copy spark file)
├── composer.json      (copy composer.json)
└── composer.lock      (copy composer.lock)
```

### Step 2: Update index.php

The `index.php` file needs path adjustments. Here's what to change:

**Original line (in public/index.php):**
```php
require FCPATH . '../app/Config/Paths.php';
```

**Change to (for flat structure):**
```php
require FCPATH . 'app/Config/Paths.php';
```

**Complete updated index.php for flat structure:**

**Option 1: Use the provided file**
- Copy `public/index_flat.php` to `htdocs/index.php` on your server
- This file is already configured for flat structure

**Option 2: Manual edit**
- Copy `public/index.php` to your deployment folder
- Change line 51 from:
  ```php
  require FCPATH . '../app/Config/Paths.php';
  ```
  To:
  ```php
  require FCPATH . 'app/Config/Paths.php';
  ```

### Step 3: Update .htaccess

The `.htaccess` file should work as-is, but verify `RewriteBase`:

```apache
RewriteBase /
```

This should already be correct in your current `.htaccess`.

### Step 4: Verify Paths.php

Check that `app/Config/Paths.php` uses relative paths correctly. It should work as-is since it uses `__DIR__` which is relative to the file location.

---

## 📤 Step 5: Upload to InfinityFree

1. **Log into InfinityFree File Manager** (or use FTP)
2. **Navigate to `htdocs/`** (this is your document root)
3. **Upload all files** maintaining the structure above
4. **Ensure file permissions:**
   - Files: `644`
   - Directories: `755`
   - `writable/`: `775` (recursive)

---

## ⚙️ Step 6: Create .env File

Create `.env` file in `htdocs/` (root level):

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://clearpay.fwh.is/'
app.appTimezone = 'Asia/Manila'

# Generate encryption key
encryption.key = base64:YOUR_GENERATED_KEY_HERE

# Database (InfinityFree uses remote MySQL - NOT localhost)
database.default.hostname = sqlXXX.infinityfree.com
database.default.database = epiz_XXXXXX_clearpay
database.default.username = epiz_XXXXXX_dbuser
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.DBDebug = false
database.default.port = 3306

# Email
email.fromEmail = 'project.clearpay@gmail.com'
email.fromName = 'ClearPay'
email.protocol = 'smtp'
email.SMTPHost = 'smtp.gmail.com'
email.SMTPUser = 'project.clearpay@gmail.com'
email.SMTPPass = 'your_app_password'
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
```

**⚠️ Critical**: Database host is NOT `localhost` - use the host provided by InfinityFree!

---

## 🔑 Step 7: Generate Encryption Key

**Option A: Via SSH (if available)**
```bash
cd htdocs
php spark key:generate
```

**Option B: Manually**
- Generate a random base64 key
- Add to `.env`: `encryption.key = base64:YOUR_KEY_HERE`

---

## 📦 Step 8: Install Dependencies

**Option A: Via SSH**
```bash
cd htdocs
composer install --no-dev --optimize-autoloader
```

**Option B: Upload vendor folder**
- Install dependencies locally: `composer install --no-dev`
- Upload entire `vendor/` folder to server

---

## 🗄️ Step 9: Database Setup

**Option A: Via SSH**
```bash
cd htdocs
php spark migrate
php spark db:seed DatabaseSeeder
```

**Option B: Via phpMyAdmin**
1. Access phpMyAdmin from InfinityFree control panel
2. Import your local database dump
3. Or manually create tables from migrations

---

## ✅ Step 10: Testing

After deployment, test:

1. **Access site**: `https://clearpay.fwh.is/`
2. **Check login page** loads
3. **Test login** with admin credentials
4. **Verify dashboard** works
5. **Test file uploads**
6. **Check API endpoints** (may be blocked by InfinityFree security)

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error

**Check:**
1. File permissions (especially `writable/`)
2. `.env` file exists and is configured
3. `index.php` paths are correct (no `../` for app folder)
4. Check `writable/logs/` for error messages

### Issue: Path Errors

**Solution:**
- Verify `index.php` uses `FCPATH . 'app/Config/Paths.php'` (no `../`)
- Check that `app/` folder is in same directory as `index.php`

### Issue: CSS/JS Not Loading

**Solution:**
- Verify `baseURL` in `app/Config/App.php` is `'https://clearpay.fwh.is/'`
- Check file permissions on `css/` and `js/` folders
- Clear browser cache

### Issue: Database Connection Failed

**Solution:**
- Verify database host is NOT `localhost`
- Use the host provided by InfinityFree (usually `sqlXXX.infinityfree.com`)
- Check database credentials in `.env`

---

## 📋 Quick Checklist

- [ ] Files uploaded to `htdocs/` (flat structure)
- [ ] `index.php` updated (removed `../` from app path)
- [ ] `.htaccess` in root (`htdocs/`)
- [ ] `.env` file created with correct database host
- [ ] File permissions set correctly
- [ ] Dependencies installed (or vendor folder uploaded)
- [ ] Database configured and migrated
- [ ] Encryption key generated
- [ ] Site tested and working

---

## ⚠️ Important Notes

1. **No `public/` folder**: Everything is in `htdocs/` root
2. **Database host**: Use InfinityFree's MySQL host (NOT localhost)
3. **API limitations**: InfinityFree's security may block API requests
4. **File permissions**: Critical for `writable/` folder
5. **SSL**: Enable free SSL in InfinityFree control panel

---

**Your structure is now ready for InfinityFree's fixed document root!** ✅

