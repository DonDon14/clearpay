# 🚀 ClearPay - Render.com Deployment

This directory contains all files and guides needed to deploy ClearPay to Render.com.

---

## 📁 Files Created

### Configuration Files
- **`render.yaml`** - Render Blueprint configuration (automated deployment)
- **`render-build.sh`** - Build script (optional, can use inline commands)
- **`render-start.sh`** - Start script (optional, can use inline commands)

### Documentation
- **`RENDER_DEPLOYMENT_GUIDE.md`** - Complete step-by-step deployment guide
- **`RENDER_QUICK_START.md`** - Quick reference for experienced users
- **`RENDER_DEPLOYMENT_CHECKLIST.md`** - Deployment checklist

---

## 🚀 Quick Start

### Option 1: Using Blueprint (Easiest)

1. **Push code to Git:**
   ```bash
   git add .
   git commit -m "Add Render deployment configuration"
   git push origin main
   ```

2. **In Render Dashboard:**
   - Click **New +** → **Blueprint**
   - Connect your repository
   - Render will detect `render.yaml`
   - Click **Apply**

3. **Configure Environment Variables:**
   - Go to web service → **Environment** tab
   - Add required variables (see guide)
   - Link database

4. **Run Migrations:**
   - Go to web service → **Shell** tab
   - Run: `php spark migrate && php spark db:seed DatabaseSeeder`

### Option 2: Manual Setup

Follow the detailed guide: [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md)

---

## 📋 What's Been Updated

### Application Configuration

1. **`app/Config/Database.php`**
   - ✅ Added support for Render environment variables
   - ✅ Reads `DB_HOST`, `DB_USER`, `DB_PASSWORD`, etc.
   - ✅ Supports `DATABASE_URL` parsing

2. **`app/Config/App.php`**
   - ✅ Added support for `APP_BASE_URL` environment variable
   - ✅ Auto-detects Render URLs

### Deployment Files

1. **`render.yaml`**
   - ✅ Web service configuration
   - ✅ Database configuration
   - ✅ Build and start commands
   - ✅ Environment variables template

2. **Build & Start Scripts**
   - ✅ `render-build.sh` - Handles Composer install and setup
   - ✅ `render-start.sh` - Starts PHP built-in server

---

## ⚙️ Required Environment Variables

Add these in Render Dashboard → Web Service → Environment:

### Application
```
CI_ENVIRONMENT = production
APP_TIMEZONE = Asia/Manila
APP_BASE_URL = https://your-service.onrender.com/
```

### Database
```
DB_HOST = (from database info)
DB_PORT = 3306
DB_NAME = clearpaydb
DB_USER = (from database info)
DB_PASSWORD = (from database password)
DB_DRIVER = MySQLi
```

### Security
```
ENCRYPTION_KEY = base64:your-generated-key
```
(Generate using: `php spark key:generate`)

### Email (Optional)
```
EMAIL_FROM = project.clearpay@gmail.com
EMAIL_SMTP_HOST = smtp.gmail.com
EMAIL_SMTP_USER = project.clearpay@gmail.com
EMAIL_SMTP_PASS = your-app-password
EMAIL_SMTP_PORT = 587
EMAIL_SMTP_CRYPTO = tls
```

---

## 🔧 Build & Start Commands

### Build Command
```bash
composer install --no-dev --optimize-autoloader && php spark key:generate --force
```

### Start Command
```bash
php -S 0.0.0.0:$PORT -t public public/index.php
```

---

## 📚 Documentation

- **Full Guide:** [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md)
- **Quick Start:** [RENDER_QUICK_START.md](RENDER_QUICK_START.md)
- **Checklist:** [RENDER_DEPLOYMENT_CHECKLIST.md](RENDER_DEPLOYMENT_CHECKLIST.md)

---

## ⚠️ Important Notes

### Free Tier Limitations

- **Sleep After Inactivity:** Services sleep after 15 minutes
- **Cold Start:** First request after sleep takes 30-60 seconds
- **Consider Upgrade:** For production, consider paid plan

### Database

- Use **internal database URL** for same-region services
- Database is automatically linked when using Blueprint
- Credentials are provided in database service info

### Environment Variables

- Set in Render Dashboard → Service → Environment tab
- Never commit `.env` file to Git
- Use Render's secure environment variables

### Auto-Deploy

- Render automatically deploys on every push to main branch
- Can trigger manual deploys
- Can rollback to previous deployment

---

## 🎯 Next Steps

1. **Read the Guide:**
   - Start with [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md)

2. **Prepare Repository:**
   - Ensure all files are committed
   - Push to GitHub/GitLab/Bitbucket

3. **Deploy:**
   - Use Blueprint (easiest) or Manual setup
   - Configure environment variables
   - Run migrations

4. **Test:**
   - Verify all functionality
   - Check logs for errors
   - Test database connections

---

## 🐛 Troubleshooting

Common issues and solutions are covered in:
- [RENDER_DEPLOYMENT_GUIDE.md - Troubleshooting Section](RENDER_DEPLOYMENT_GUIDE.md#troubleshooting)

---

## 📞 Support

- **Render Docs:** https://render.com/docs
- **Render Community:** https://community.render.com
- **CodeIgniter Docs:** https://codeigniter.com/user_guide/

---

**Last Updated:** 2024
**Version:** 1.0

