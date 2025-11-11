# InfinityFree Quick Start Guide
## Flat Structure Deployment (Document Root Cannot Be Changed)

Since InfinityFree doesn't allow changing the document root, use this **flat structure** approach.

---

## 📁 Upload Structure

Upload everything to `htdocs/` (document root):

```
htdocs/
├── .htaccess          ← From public/.htaccess
├── index.php          ← Use public/index_flat.php (or modify public/index.php)
├── favicon.ico        ← From public/favicon.ico
├── robots.txt         ← From public/robots.txt
├── css/               ← From public/css/
├── js/                ← From public/js/
├── uploads/           ← From public/uploads/
├── app/               ← Entire app/ folder
├── vendor/            ← Entire vendor/ folder (or install on server)
├── writable/          ← Entire writable/ folder
├── spark              ← spark file
├── composer.json      ← composer.json
└── .env               ← Create on server
```

---

## 🔧 Critical Changes

### 1. index.php Path Update

**IMPORTANT**: The `index.php` file needs one change for flat structure.

**Option A: Use provided file**
- Copy `public/index_flat.php` → rename to `index.php` → upload to `htdocs/`

**Option B: Edit manually**
- In `index.php`, change line 51:
  - **From:** `require FCPATH . '../app/Config/Paths.php';`
  - **To:** `require FCPATH . 'app/Config/Paths.php';`

### 2. .env Configuration

Create `.env` in `htdocs/` root:

```env
CI_ENVIRONMENT = production

app.baseURL = 'https://clearpay.fwh.is/'
app.appTimezone = 'Asia/Manila'

encryption.key = base64:YOUR_GENERATED_KEY

# Database - CRITICAL: Use InfinityFree's MySQL host (NOT localhost!)
database.default.hostname = sqlXXX.infinityfree.com
database.default.database = epiz_XXXXXX_clearpay
database.default.username = epiz_XXXXXX_dbuser
database.default.password = your_password
database.default.DBDriver = MySQLi
database.default.DBDebug = false
```

**⚠️ Database host is NOT `localhost` - check InfinityFree control panel for correct host!**

---

## 📤 Upload Steps

1. **Prepare files locally:**
   - Copy files from `public/` to root level
   - Use `public/index_flat.php` as `index.php` (or edit manually)
   - Keep `app/`, `vendor/`, `writable/` as-is

2. **Upload to InfinityFree:**
   - Upload all files to `htdocs/`
   - Set permissions: files `644`, folders `755`, `writable/` `775`

3. **Create `.env` file:**
   - Create in `htdocs/` root
   - Add database credentials (use InfinityFree's MySQL host!)

4. **Install dependencies:**
   - Via SSH: `composer install --no-dev`
   - Or upload `vendor/` folder from local

5. **Setup database:**
   - Via SSH: `php spark migrate` and `php spark db:seed DatabaseSeeder`
   - Or import via phpMyAdmin

---

## ✅ Quick Checklist

- [ ] Files uploaded to `htdocs/` (flat structure, no `public/` folder)
- [ ] `index.php` updated (removed `../` from app path)
- [ ] `.htaccess` in root
- [ ] `.env` created with correct database host (NOT localhost!)
- [ ] File permissions set (`writable/` = 775)
- [ ] Dependencies installed
- [ ] Database configured
- [ ] Site tested

---

## 🐛 Common Issues

### ERR_CONNECTION_TIMED_OUT (Most Common!)

**This means browser can't reach the server - it's a DNS/domain issue, NOT a CodeIgniter issue!**

**Quick Checks:**
1. ✅ **Is domain added to InfinityFree account?**
   - Log into InfinityFree control panel
   - Go to "Domains" → Check if `clearpay.fwh.is` is listed
   - If not, add it first!

2. ✅ **Are DNS records configured?**
   - Check domain registrar (where you bought the domain)
   - DNS should point to InfinityFree (nameservers or A record)
   - Test: `nslookup clearpay.fwh.is` (should show InfinityFree IP)

3. ✅ **Wait for DNS propagation**
   - DNS changes take 24-48 hours to propagate
   - If you just set up DNS, wait and try again
   - Check: [whatsmydns.net](https://www.whatsmydns.net)

4. ✅ **Try InfinityFree temporary URL**
   - Check InfinityFree control panel for temporary URL
   - If temporary URL works → problem is DNS/domain
   - If temporary URL doesn't work → problem is account/files

**See `TROUBLESHOOTING_CONNECTION_TIMEOUT.md` for detailed help!**

---

**500 Error?**
- Check `writable/` permissions (775)
- Check `.env` exists
- Check `index.php` path is correct

**Database Error?**
- Database host is NOT `localhost`
- Use InfinityFree's MySQL host from control panel

**CSS/JS Not Loading?**
- Check `baseURL` in `app/Config/App.php`
- Clear browser cache

---

## ⚠️ IMPORTANT: Fix Domain/DNS First!

**Before uploading files, make sure:**
1. Domain is added to InfinityFree account
2. DNS is configured correctly
3. Domain resolves (check with `nslookup`)
4. SSL is enabled (if using HTTPS)

**Only after domain works should you upload files!**

---

**That's it! Your site should work at `https://clearpay.fwh.is/`** ✅

**If you see connection timeout, check `TROUBLESHOOTING_CONNECTION_TIMEOUT.md` first!**

