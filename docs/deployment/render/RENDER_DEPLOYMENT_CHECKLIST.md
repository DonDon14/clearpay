# ✅ Render.com Deployment Checklist

Use this checklist to ensure a smooth deployment of ClearPay to Render.com.

---

## 📋 Pre-Deployment

### Repository Preparation
- [ ] Code is in Git repository (GitHub/GitLab/Bitbucket)
- [ ] All changes committed and pushed
- [ ] `.env` file is NOT committed (in .gitignore)
- [ ] `render.yaml` exists in repository
- [ ] `composer.json` is up to date
- [ ] Application tested locally

### Files Verification
- [ ] `public/index.php` exists
- [ ] `app/` directory exists
- [ ] `composer.json` exists
- [ ] `render.yaml` exists
- [ ] `render-build.sh` exists (optional)
- [ ] `render-start.sh` exists (optional)

---

## 🔐 Render Account Setup

### Account Creation
- [ ] Render.com account created
- [ ] Email verified
- [ ] GitHub/GitLab/Bitbucket connected
- [ ] Repository access granted

---

## 🚀 Web Service Deployment

### Service Creation
- [ ] Web service created (Blueprint or Manual)
- [ ] Repository connected
- [ ] Correct branch selected (main/master)
- [ ] Environment set to `PHP`
- [ ] Region selected (closest to users)

### Build Configuration
- [ ] Build command configured:
  ```bash
  composer install --no-dev --optimize-autoloader && php spark key:generate --force
  ```
- [ ] Start command configured:
  ```bash
  php -S 0.0.0.0:$PORT -t public public/index.php
  ```
- [ ] Health check path set to `/`
- [ ] Root directory correct (empty or `./`)

### Service Settings
- [ ] Plan selected (Starter/Standard/Pro)
- [ ] Auto-deploy enabled (optional)
- [ ] Service name set: `clearpay-web`

---

## 🗄️ Database Setup

### Database Creation
- [ ] Database service created
- [ ] Database type selected (MySQL/PostgreSQL)
- [ ] Database name set: `clearpaydb`
- [ ] Database user created
- [ ] Region matches web service region
- [ ] Plan selected (Starter/Standard/Pro)

### Database Connection
- [ ] Database credentials noted
- [ ] Internal connection URL obtained
- [ ] Database linked to web service
- [ ] `DATABASE_URL` environment variable added (auto)

---

## ⚙️ Environment Variables

### Application Settings
- [ ] `CI_ENVIRONMENT = production` set
- [ ] `APP_TIMEZONE = Asia/Manila` set
- [ ] `APP_BASE_URL` set (Render URL or custom domain)

### Database Configuration
- [ ] `DB_HOST` set (from database info)
- [ ] `DB_PORT` set (usually 3306)
- [ ] `DB_NAME` set (clearpaydb)
- [ ] `DB_USER` set (from database info)
- [ ] `DB_PASSWORD` set (from database info)
- [ ] `DB_DRIVER = MySQLi` set

### Security
- [ ] `ENCRYPTION_KEY` generated and set
- [ ] Key format: `base64:...`

### Email Configuration (Optional)
- [ ] `EMAIL_FROM` set
- [ ] `EMAIL_FROM_NAME` set
- [ ] `EMAIL_SMTP_HOST` set
- [ ] `EMAIL_SMTP_USER` set
- [ ] `EMAIL_SMTP_PASS` set
- [ ] `EMAIL_SMTP_PORT` set (587)
- [ ] `EMAIL_SMTP_CRYPTO` set (tls)

---

## 🔄 Database Migrations

### Run Migrations
- [ ] Render Shell accessed
- [ ] Migrations run: `php spark migrate`
- [ ] Migrations completed successfully
- [ ] Seeders run: `php spark db:seed DatabaseSeeder`
- [ ] Seeders completed successfully

### Verification
- [ ] Database tables created
- [ ] Initial data seeded
- [ ] Database connection tested
- [ ] No migration errors

---

## 🌍 Custom Domain (Optional)

### Domain Setup
- [ ] Custom domain added in service settings
- [ ] DNS records updated (CNAME)
- [ ] DNS propagation verified
- [ ] SSL certificate provisioned (automatic)
- [ ] HTTPS working
- [ ] `APP_BASE_URL` updated to custom domain

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
- [ ] Email sending works (if configured)
- [ ] All routes accessible
- [ ] Session management works

### Security Checks
- [ ] HTTPS enforced (if custom domain)
- [ ] No sensitive information exposed
- [ ] Error messages don't reveal system info
- [ ] Environment variables secure

---

## 📊 Monitoring

### Logs
- [ ] Logs accessible in dashboard
- [ ] No critical errors in logs
- [ ] Performance acceptable
- [ ] Error rates monitored

### Service Health
- [ ] Service shows "Live" status
- [ ] Health checks passing
- [ ] Uptime acceptable
- [ ] Response times acceptable

---

## 🔧 Post-Deployment

### Configuration
- [ ] Base URL updated (if custom domain)
- [ ] All environment variables verified
- [ ] Database backups configured
- [ ] Monitoring set up

### Documentation
- [ ] Deployment notes documented
- [ ] Credentials stored securely
- [ ] Team members informed
- [ ] Access credentials shared (if needed)

### Optimization
- [ ] Caching enabled (if applicable)
- [ ] Performance optimized
- [ ] Dependencies updated
- [ ] Security patches applied

---

## 🐛 Troubleshooting (if issues found)

### Common Issues
- [ ] Build errors resolved
- [ ] Start command issues resolved
- [ ] Database connection issues resolved
- [ ] Environment variable issues resolved
- [ ] Permission issues resolved
- [ ] Routing issues resolved

---

## 📝 Notes

**Date Deployed:** _______________

**Deployed By:** _______________

**Render Service URL:** _______________

**Database Name:** _______________

**Custom Domain:** _______________

**Special Notes:**
_________________________________
_________________________________
_________________________________

---

## 🎉 Deployment Complete!

Once all items are checked, your ClearPay application should be live on Render.com!

**Next Steps:**
1. Monitor the application for the first 24-48 hours
2. Set up regular backups
3. Keep dependencies updated
4. Monitor error logs regularly
5. Consider upgrading plan if free tier limitations affect you

---

## ⚠️ Free Tier Limitations

- **Sleep After Inactivity:** Services sleep after 15 minutes
- **Cold Start:** First request after sleep is slow (30-60 seconds)
- **Consider Upgrade:** For production, consider paid plan

---

**Last Updated:** 2024

