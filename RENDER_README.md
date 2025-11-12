# 🚀 ClearPay - Render.com Deployment

This directory contains all files and guides needed to deploy ClearPay to Render.com.

---

## 📁 Files Created

### Configuration Files
- **`render.yaml`** - Render Blueprint configuration (uses Docker)
- **`Dockerfile`** - Docker image configuration (required for PHP on Render)
- **`.dockerignore`** - Files excluded from Docker build
- **`render-build.sh`** - Build script (optional, not used with Docker)
- **`render-start.sh`** - Start script (optional, not used with Docker)

### Documentation
- **`RENDER_DEPLOYMENT_GUIDE.md`** - Complete step-by-step deployment guide
- **`RENDER_QUICK_START.md`** - Quick reference for experienced users
- **`RENDER_DEPLOYMENT_CHECKLIST.md`** - Deployment checklist

---

## 🚀 Quick Start

### Important: PHP Requires Docker

Render.com does NOT have native PHP support. We use Docker to deploy the application.

### Option 1: Using Blueprint (Easiest)

1. **Push code to Git:**
   ```bash
   git add Dockerfile .dockerignore render.yaml
   git commit -m "Add Render Docker deployment configuration"
   git push origin main
   ```

2. **In Render Dashboard:**
   - Click **New +** → **Blueprint**
   - Connect your repository
   - Render will detect `render.yaml`
   - Click **Apply**
   - Wait for Docker build (5-10 minutes first time)

3. **Configure Environment Variables:**
   - Go to web service → **Environment** tab
   - Add required variables (see guide)
   - Link database

4. **Generate Encryption Key:**
   - Go to web service → **Shell** tab
   - Run: `php spark key:generate`
   - Copy the key and add to environment variables

5. **Run Migrations:**
   - In Shell tab, run: `php spark migrate && php spark db:seed DatabaseSeeder`

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

### Docker Configuration

1. **`Dockerfile`**
   - ✅ PHP 8.2 with Apache
   - ✅ Required PHP extensions installed
   - ✅ Composer installed
   - ✅ Document root set to `public/`
   - ✅ Permissions configured

2. **`.dockerignore`**
   - ✅ Excludes unnecessary files from build
   - ✅ Reduces Docker image size

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

### Docker Configuration
- **Dockerfile:** Builds PHP 8.2 with Apache
- **Document Root:** `public/` directory
- **Build:** Handled automatically by Dockerfile
- **Start:** Apache serves the application

---

## 📚 Documentation

- **Full Guide:** [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md)
- **Quick Start:** [RENDER_QUICK_START.md](RENDER_QUICK_START.md)
- **Checklist:** [RENDER_DEPLOYMENT_CHECKLIST.md](RENDER_DEPLOYMENT_CHECKLIST.md)
- **Docker Setup:** [RENDER_DOCKER_SETUP.md](RENDER_DOCKER_SETUP.md)

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

