# 🔄 How to Run Migrations & Seeders on Render.com

Step-by-step guide on where and how to run database migrations and seeders after deploying to Render.

---

## 📍 Where to Run Commands

### Option 1: Render Shell (Recommended)

**Location:** Render Dashboard → Your Web Service → **Shell** tab

This is the easiest way to run commands directly in your deployed application.

---

## 🚀 Step-by-Step Instructions

### Step 1: Access Render Dashboard

1. Go to https://dashboard.render.com
2. Log in to your account
3. Find your **ClearPay** web service
4. Click on the service name to open it

### Step 2: Open Shell Tab

1. In your web service page, look for tabs at the top:
   - **Logs**
   - **Metrics**
   - **Environment**
   - **Shell** ← **Click this one!**
   - **Settings**

2. Click on **Shell** tab

3. You'll see a terminal/command prompt interface

### Step 3: Run Migrations

In the Shell terminal, type:

```bash
php spark migrate
```

Press **Enter** and wait for migrations to complete.

**Expected output:**
```
Running migrations...
Migration: 2024-01-01-120000_CreateUsersTable
Migration: 2024-01-01-120001_CreatePaymentsTable
...
All migrations completed successfully.
```

### Step 4: Run Seeders

After migrations complete, run:

```bash
php spark db:seed DatabaseSeeder
```

Press **Enter** and wait for seeding to complete.

**Expected output:**
```
Seeding: UserSeeder
Seeding: PaymentMethodSeeder
Seeding: ContributionSeeder
...
All seeders completed successfully.
```

### Step 5: Verify Setup (Optional)

Check if everything worked:

```bash
php spark db:table
```

This will list all tables in your database.

---

## 📸 Visual Guide

```
Render Dashboard
    │
    ├── My Workspace
    │   │
    │   └── clearpay-web (your service)
    │       │
    │       ├── [Logs] tab
    │       ├── [Metrics] tab
    │       ├── [Environment] tab
    │       ├── [Shell] tab ← CLICK HERE!
    │       └── [Settings] tab
    │
    └── ...
```

**In Shell Tab:**
```
┌─────────────────────────────────────┐
│  Render Shell                       │
├─────────────────────────────────────┤
│  $ php spark migrate                │
│  Running migrations...             │
│  ✓ Migration completed              │
│                                     │
│  $ php spark db:seed DatabaseSeeder │
│  Seeding database...                │
│  ✓ Seeding completed                │
│                                     │
│  $ _                                 │
└─────────────────────────────────────┘
```

---

## 🔧 Alternative: Using Render CLI

If you prefer command line, you can also use Render CLI:

### Install Render CLI

```bash
# macOS/Linux
brew install render

# Or download from: https://render.com/docs/cli
```

### Connect and Run Commands

```bash
# Login to Render
render login

# Connect to your service shell
render shell clearpay-web

# Now you're in the shell, run commands:
php spark migrate
php spark db:seed DatabaseSeeder
```

---

## ⚠️ Important Notes

### When to Run

- **After first deployment:** Run migrations and seeders immediately
- **After code updates:** Only if you added new migrations
- **After database reset:** Run both migrations and seeders

### Order Matters

**Always run in this order:**
1. ✅ Migrations first (`php spark migrate`)
2. ✅ Seeders second (`php spark db:seed DatabaseSeeder`)

**Why?**
- Migrations create the table structure
- Seeders populate data into those tables

### What Gets Created

**Migrations create:**
- Database tables (users, payments, contributions, etc.)
- Table structure and relationships
- Indexes and constraints

**Seeders create:**
- Admin user account
- Payment methods (GCash, PayMaya, Bank Transfer, Cash, etc.)
- Sample contributions
- Initial data needed for the app to work

---

## 🐛 Troubleshooting

### Issue: "Command not found: php"

**Solution:**
- Make sure you're in the Shell tab of your **web service** (not database)
- The web service has PHP installed
- Try: `which php` to verify PHP is available

### Issue: "Database connection failed"

**Solution:**
1. Check environment variables are set:
   - Go to **Environment** tab
   - Verify `DATABASE_URL` is set (auto-set when database is linked)
2. Verify database is running:
   - Go to your database service
   - Check it shows "Available" status
3. Check database region matches web service region

### Issue: "Migration already run"

**Solution:**
- This is normal if migrations were already run
- You can safely skip or use: `php spark migrate -f` to force

### Issue: "Seeder fails"

**Solution:**
- Make sure migrations ran successfully first
- Check error message for specific issue
- Verify database connection works
- Try running individual seeders:
  ```bash
  php spark db:seed UserSeeder
  php spark db:seed PaymentMethodSeeder
  ```

---

## ✅ Verification Checklist

After running migrations and seeders:

- [ ] Migrations completed without errors
- [ ] Seeders completed without errors
- [ ] Can access your application URL
- [ ] Can log in with admin account
- [ ] Payment methods are available (check in app)
- [ ] No database errors in logs

---

## 📝 Quick Reference

### Commands Summary

```bash
# 1. Run migrations (creates tables)
php spark migrate

# 2. Run seeders (populates data)
php spark db:seed DatabaseSeeder

# 3. Check tables
php spark db:table

# 4. Generate encryption key (if needed)
php spark key:generate

# 5. Clear cache
php spark cache:clear
```

### Where to Find Shell

1. Render Dashboard
2. Click your web service (`clearpay-web`)
3. Click **Shell** tab
4. Type commands in the terminal

---

## 🎯 Next Steps After Running Migrations

1. **Test your application:**
   - Visit your Render URL
   - Try logging in
   - Test creating a payment

2. **Verify data:**
   - Check payment methods exist
   - Verify admin user can log in
   - Test database operations

3. **Monitor logs:**
   - Check **Logs** tab for any errors
   - Verify no database connection issues

---

**Last Updated:** 2024

