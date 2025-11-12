# ⚡ Render.com Quick Start Guide

Quick reference for deploying ClearPay to Render.com. For detailed instructions, see [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md).

---

## 🚀 Quick Steps

### 1. Sign Up & Connect
- Go to https://render.com
- Sign up with GitHub
- Connect your repository

### 2. Deploy Using Blueprint (Easiest)

1. **Create Blueprint:**
   - Click **New +** → **Blueprint**
   - Connect repository
   - Render detects `render.yaml` automatically

2. **Apply:**
   - Review configuration
   - Click **Apply**
   - Wait for deployment

### 3. Manual Setup (Alternative)

**Create Web Service:**
- **Name:** `clearpay-web`
- **Environment:** `PHP`
- **Build Command:**
  ```bash
  composer install --no-dev --optimize-autoloader && php spark key:generate --force
  ```
- **Start Command:**
  ```bash
  php -S 0.0.0.0:$PORT -t public public/index.php
  ```

**Create Database:**
- **Type:** MySQL
- **Name:** `clearpay-db`
- **Plan:** Starter (free)

### 4. Configure Environment Variables

In web service → **Environment** tab, add:

```
CI_ENVIRONMENT = production
APP_TIMEZONE = Asia/Manila
APP_BASE_URL = https://your-service.onrender.com/

DB_HOST = (from database info)
DB_PORT = 3306
DB_NAME = clearpaydb
DB_USER = (from database info)
DB_PASSWORD = (from database password)

ENCRYPTION_KEY = base64:your-key-here
```

### 5. Link Database

- In web service → **Environment** tab
- Scroll to **Database** section
- Click **Link Database**
- Select your database

### 6. Run Migrations

- Go to web service → **Shell** tab
- Run:
  ```bash
  php spark migrate
  php spark db:seed DatabaseSeeder
  ```

### 7. Test

- Visit your Render URL
- Test login and functionality

---

## 🔧 Common Commands

### Access Shell
- Dashboard → Service → **Shell** tab

### Run Migrations
```bash
php spark migrate
php spark db:seed DatabaseSeeder
```

### Generate Encryption Key
```bash
php spark key:generate
```

### Check Database
```bash
php spark db:table
```

---

## ⚙️ Environment Variables Template

```env
CI_ENVIRONMENT = production
APP_TIMEZONE = Asia/Manila
APP_BASE_URL = https://your-service.onrender.com/

DB_HOST = your-db-host.render.com
DB_PORT = 3306
DB_NAME = clearpaydb
DB_USER = your-db-user
DB_PASSWORD = your-db-password
DB_DRIVER = MySQLi

ENCRYPTION_KEY = base64:your-generated-key

# Email (Optional)
EMAIL_FROM = project.clearpay@gmail.com
EMAIL_SMTP_HOST = smtp.gmail.com
EMAIL_SMTP_USER = project.clearpay@gmail.com
EMAIL_SMTP_PASS = your-app-password
EMAIL_SMTP_PORT = 587
```

---

## 🐛 Quick Troubleshooting

| Issue | Solution |
|-------|----------|
| Build fails | Check build command, verify composer.json |
| Won't start | Verify start command, check public/index.php |
| Database error | Check credentials, verify database is linked |
| 404 errors | Check .htaccess, verify document root |
| Upload fails | Check writable/uploads/ permissions |

---

## ⚠️ Important Notes

1. **Free Tier Sleep:** Services sleep after 15 min inactivity
2. **Cold Start:** First request after sleep is slow (30-60s)
3. **Database:** Use internal connection for same region
4. **Environment:** Never commit .env to Git
5. **Auto-Deploy:** Deploys on every push to main branch

---

## 📞 Need Help?

- **Full Guide:** [RENDER_DEPLOYMENT_GUIDE.md](RENDER_DEPLOYMENT_GUIDE.md)
- **Checklist:** [RENDER_DEPLOYMENT_CHECKLIST.md](RENDER_DEPLOYMENT_CHECKLIST.md)
- **Render Docs:** https://render.com/docs

---

**Last Updated:** 2024

