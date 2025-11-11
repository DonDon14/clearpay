# ClearPay Deployment Checklist
## Quick Reference for clearpay.fwh.is

Use this checklist during deployment to ensure nothing is missed.

---

## ✅ Pre-Upload Preparation

- [ ] All configuration files updated for production
- [ ] Base URL changed to `https://clearpay.fwh.is/`
- [ ] HTTPS enforcement enabled
- [ ] CORS configured for production domain
- [ ] Tested locally (if possible)

---

## ✅ File Upload

- [ ] Uploaded `app/` directory
- [ ] Uploaded `public/` directory (set as document root)
- [ ] Uploaded `vendor/` directory (or install via Composer)
- [ ] Uploaded `writable/` directory (with proper permissions)
- [ ] Uploaded `spark` file
- [ ] Uploaded `composer.json` and `composer.lock`
- [ ] Excluded `flutter_app/`, `tests/`, `.git/`, IDE files

---

## ✅ Server Configuration

- [ ] Document root points to `public/` directory
- [ ] SSL certificate installed and working
- [ ] Apache mod_rewrite enabled
- [ ] PHP version 8.1+ installed
- [ ] Required PHP extensions installed

---

## ✅ Database Setup

- [ ] Database created
- [ ] Database user created with proper permissions
- [ ] Database credentials noted
- [ ] Migrations run: `php spark migrate`
- [ ] Seeders run: `php spark db:seed DatabaseSeeder`
- [ ] Database connection tested

---

## ✅ Environment Configuration

- [ ] `.env` file created in root directory
- [ ] `CI_ENVIRONMENT = production` set
- [ ] `app.baseURL = 'https://clearpay.fwh.is/'` set
- [ ] Database credentials configured
- [ ] Encryption key generated: `php spark key:generate`
- [ ] Email settings configured (if needed)

---

## ✅ File Permissions

- [ ] All directories set to `755`
- [ ] All files set to `644`
- [ ] `writable/` directory set to `775` (recursive)
- [ ] `spark` file is executable (`+x`)
- [ ] Web server user owns `writable/` directory

---

## ✅ Dependencies

- [ ] Composer dependencies installed: `composer install --no-dev`
- [ ] Vendor directory uploaded (if installed locally)

---

## ✅ Testing

- [ ] Site accessible at `https://clearpay.fwh.is/`
- [ ] HTTPS redirect working
- [ ] Login page loads
- [ ] Can log in with admin credentials
- [ ] Dashboard loads correctly
- [ ] File uploads work
- [ ] Database operations work
- [ ] Email sending works (if configured)
- [ ] API endpoints accessible (for Flutter app)
- [ ] CORS headers present in API responses

---

## ✅ Security

- [ ] `.env` file not accessible via web
- [ ] Default admin password changed
- [ ] `writable/` directory protected
- [ ] Error display disabled in production
- [ ] SSL certificate valid

---

## ✅ Post-Deployment

- [ ] Flutter app API URL updated
- [ ] Backup strategy configured
- [ ] Log monitoring set up
- [ ] Performance optimization done (if needed)

---

## 🚨 Common Issues to Check

If something doesn't work:

1. **500 Error**: Check `writable/logs/` for errors
2. **Database Error**: Verify `.env` database credentials
3. **CSS/JS Not Loading**: Check `baseURL` in `App.php`
4. **Uploads Not Working**: Check `writable/uploads/` permissions
5. **HTTPS Issues**: Verify SSL certificate and `.htaccess`

---

**Ready to Deploy!** 🚀

