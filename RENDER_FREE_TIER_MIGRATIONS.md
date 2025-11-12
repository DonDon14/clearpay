# 🆓 Running Migrations on Render Free Tier (No Shell Access)

Since Render's **Shell feature is only available on paid plans**, we've set up **automatic migrations** that run when your application starts.

---

## ✅ Automatic Migration Setup

### What Happens Automatically

When your application starts on Render, it will:

1. **Wait for database** to be ready
2. **Run migrations** automatically (`php spark migrate`)
3. **Run seeders** automatically if database is empty (`php spark db:seed DatabaseSeeder`)
4. **Start Apache** and serve your application

### Files Created

- **`docker-entrypoint.sh`** - Startup script that runs migrations
- **`Dockerfile`** - Updated to use the entrypoint script

---

## 🔄 How It Works

### First Deployment

1. Render builds your Docker image
2. Container starts
3. Script waits for database connection
4. Script runs migrations (creates tables)
5. Script runs seeders (populates data)
6. Apache starts serving your app

### Subsequent Deployments

1. Container starts
2. Script checks if migrations already ran
3. Only runs new migrations (if any)
4. Skips seeders (data already exists)
5. Apache starts

---

## 🎯 What You Need to Do

### Option 1: Automatic (Recommended)

**Nothing!** Migrations run automatically on first startup.

Just:
1. Deploy your application
2. Wait for it to start (first time takes longer)
3. Check logs to see migrations running
4. Visit your app URL

### Option 2: Manual Trigger (Alternative)

If automatic migrations don't work, you can create a simple admin endpoint:

**Create:** `app/Controllers/Admin/Setup.php`

```php
<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Setup extends Controller
{
    public function runMigrations()
    {
        // Add password protection!
        $password = $this->request->getPost('password');
        if ($password !== 'your-secret-password-here') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        // Run migrations
        $migrate = \Config\Services::migrations();
        $migrate->setNamespace(null)->latest();

        // Run seeders
        $seeder = \Config\Database::seeder();
        $seeder->call('DatabaseSeeder');

        return $this->response->setJSON(['success' => 'Migrations and seeders completed']);
    }
}
```

Then visit: `https://your-app.onrender.com/admin/setup/runMigrations` (with password)

---

## 📊 Checking if Migrations Ran

### Method 1: Check Logs

1. Go to Render Dashboard
2. Click your web service (`clearpay-web`)
3. Click **Logs** tab
4. Look for messages like:
   ```
   🔄 Checking if migrations need to run...
   ✅ Migrations completed
   🌱 Running seeders...
   ✅ Setup complete!
   ```

### Method 2: Test Your App

1. Visit your app URL
2. Try to log in
3. If login works → migrations ran successfully
4. If you see database errors → migrations may have failed

### Method 3: Check Database (if you have access)

- Use a database client (like pgAdmin for PostgreSQL)
- Connect to your Render database
- Check if tables exist

---

## 🐛 Troubleshooting

### Issue: Migrations Not Running

**Symptoms:**
- App starts but shows database errors
- Tables don't exist

**Solutions:**

1. **Check Logs:**
   - Go to **Logs** tab
   - Look for error messages
   - Check database connection errors

2. **Verify Environment Variables:**
   - Go to **Environment** tab
   - Check `DATABASE_URL` is set
   - Verify database is linked to web service

3. **Check Database Status:**
   - Go to your database service
   - Verify it's "Available"
   - Check region matches web service

4. **Manual Trigger:**
   - Use the admin endpoint method (Option 2 above)
   - Or upgrade to paid plan for Shell access

### Issue: Migrations Run Every Time

**Symptoms:**
- Slow startup times
- Logs show migrations running on every restart

**Solution:**
- This is normal for first few starts
- After migrations complete, they won't run again
- Check migration logs to see what's happening

### Issue: Seeders Run Multiple Times

**Symptoms:**
- Duplicate data in database
- Seeders running on every restart

**Solution:**
- The script checks if data exists before seeding
- If you see duplicates, the check may have failed
- You may need to manually clean the database

---

## 🔧 Manual Override

If you need to manually run migrations:

### Option 1: Create Admin Endpoint (Free)

Create a controller that runs migrations when you visit a URL (with password protection).

### Option 2: Upgrade to Paid Plan

Upgrade to a paid plan to get Shell access:
- **Starter Plan:** $7/month
- Includes Shell access
- No sleep (always on)
- Better performance

### Option 3: Use Render CLI (Free)

Render CLI might work even on free tier:

```bash
# Install Render CLI
npm install -g render-cli

# Login
render login

# Connect to service (may not work on free tier)
render shell clearpay-web
```

---

## ✅ Verification Checklist

After deployment:

- [ ] App starts without errors
- [ ] Logs show "Database is ready"
- [ ] Logs show "Migrations completed" or "already run"
- [ ] Logs show "Seeders completed" or "already seeded"
- [ ] Can access app URL
- [ ] Can log in with admin account
- [ ] Payment methods are available
- [ ] No database errors

---

## 📝 Important Notes

### Free Tier Limitations

- **No Shell Access:** Can't run commands manually
- **Sleep After Inactivity:** Service sleeps after 15 minutes
- **Cold Start:** First request after sleep is slow (30-60 seconds)
- **Migrations on Every Cold Start:** May run migrations again (but should be quick)

### Security

- The entrypoint script runs migrations automatically
- Make sure your database credentials are secure
- Don't expose migration endpoints publicly without authentication

### Performance

- First startup takes longer (runs migrations)
- Subsequent starts are faster (migrations already run)
- Migrations only run if needed

---

## 🎯 Summary

**For Free Tier:**
- ✅ Migrations run automatically on startup
- ✅ No manual intervention needed
- ✅ Works on first deployment
- ❌ Can't manually trigger via Shell (need paid plan)

**If You Need Manual Control:**
- Create admin endpoint (free)
- Upgrade to paid plan ($7/month)
- Use Render CLI (may work on free tier)

---

**Last Updated:** 2024

