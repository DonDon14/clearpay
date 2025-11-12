# 🐳 Render.com Docker Setup for ClearPay

Render.com doesn't have native PHP support, so we use Docker to deploy the application.

---

## 📋 What's Included

### Docker Files
- **`Dockerfile`** - Docker image configuration
- **`.dockerignore`** - Files to exclude from Docker build
- **`render.yaml`** - Updated to use Docker

---

## 🔧 Dockerfile Overview

The Dockerfile:
- Uses PHP 8.2 with Apache
- Installs required PHP extensions (MySQL, GD, etc.)
- Installs Composer
- Copies application files
- Sets up Apache with correct document root (`public/`)
- Configures permissions for writable directories

---

## 🚀 Deployment Steps

### Step 1: Verify Files

Ensure these files exist:
- ✅ `Dockerfile`
- ✅ `.dockerignore`
- ✅ `render.yaml` (updated for Docker)

### Step 2: Push to Git

```bash
git add Dockerfile .dockerignore render.yaml
git commit -m "Add Docker configuration for Render"
git push origin main
```

### Step 3: Deploy on Render

1. **Create Blueprint:**
   - Go to Render Dashboard
   - Click **New +** → **Blueprint**
   - Connect your repository
   - Render will detect `render.yaml`

2. **Apply Blueprint:**
   - Review configuration
   - Click **Apply**
   - Render will build Docker image

3. **Wait for Build:**
   - First build takes 5-10 minutes
   - Monitor build logs
   - Wait for "Live" status

---

## ⚙️ Environment Variables

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

---

## 🔄 Database Migrations

After deployment:

1. **Access Shell:**
   - Go to web service → **Shell** tab
   - Or use Render's SSH feature

2. **Run Migrations:**
   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

---

## 🐛 Troubleshooting

### Build Fails

**Check:**
- Dockerfile syntax is correct
- All required files are in repository
- Build logs for specific errors

**Common Issues:**
- Missing PHP extensions → Add to Dockerfile
- Composer errors → Check composer.json
- Permission errors → Check Dockerfile permissions

### Application Won't Start

**Check:**
- Apache is starting correctly
- Document root is set to `public/`
- Environment variables are set
- Database connection works

### Database Connection Issues

**Solutions:**
- Verify database credentials
- Use internal database URL for same region
- Check database is running
- Verify environment variables

---

## 📝 Notes

### Docker Build Process

1. **Build Stage:**
   - Installs system dependencies
   - Installs PHP extensions
   - Installs Composer
   - Copies application files
   - Runs `composer install`

2. **Runtime:**
   - Apache serves from `public/` directory
   - PHP processes requests
   - Writable directories have correct permissions

### Performance

- **First Build:** 5-10 minutes (downloads base image)
- **Subsequent Builds:** 2-5 minutes (uses cache)
- **Startup Time:** 10-30 seconds

### Free Tier

- Services sleep after 15 minutes
- First request after sleep: 30-60 seconds
- Consider paid plan for production

---

## 🔗 Related Files

- **Dockerfile** - Docker image configuration
- **.dockerignore** - Excluded files
- **render.yaml** - Render service configuration
- **RENDER_DEPLOYMENT_GUIDE.md** - Full deployment guide

---

**Last Updated:** 2024

