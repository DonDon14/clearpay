# Setup Verification Guide - Ensure 100% Working After Pull

## Critical Differences Between Working and Non-Working Devices

When you pull from GitHub, the following **MUST** be done on the new device:

### 1. ✅ Database Migrations (Creates Tables)
```bash
php spark migrate
```
**Why:** Creates all database tables. Without this, tables don't exist.

### 2. ✅ Database Seeders (Populates Initial Data) - **CRITICAL**
```bash
php spark db:seed DatabaseSeeder
```
**Why:** This seeds:
- Users (admin account)
- Contributions
- **Payment Methods** ← **THIS IS THE KEY ISSUE!**

Without payment methods seeded, validation fails because there are no valid payment methods in the database.

### 3. ✅ Verify Payment Methods Exist
After seeding, verify payment methods are in the database:
```sql
SELECT * FROM payment_methods;
```

You should see at least:
- GCash
- PayMaya
- Bank Transfer
- Cash
- Online Banking

### 4. ✅ Environment Configuration
Check if `.env` file exists. If not, create it:
```bash
# Copy from .env.example if it exists, or create new
```

Minimum required `.env` settings:
```env
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost/ClearPay/public/'
app.appTimezone = 'Asia/Manila'

# Database
database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
```

### 5. ✅ Composer Dependencies
```bash
composer install
```
**Why:** Installs all PHP dependencies. Without this, the app won't run.

### 6. ✅ Generate Encryption Key
```bash
php spark key:generate
```
**Why:** Required for sessions and encryption. Without this, sessions won't work.

## Complete Setup Checklist for New Device

After pulling from GitHub, run these commands **IN ORDER**:

```bash
# 1. Install PHP dependencies
composer install

# 2. Create database (via phpMyAdmin or command line)
# Database name: clearpaydb
# Collation: utf8mb4_general_ci

# 3. Run migrations (creates tables)
php spark migrate

# 4. Seed database (populates initial data - CRITICAL!)
php spark db:seed DatabaseSeeder

# 5. Verify payment methods were seeded
php spark db:table payment_methods
# Or check in phpMyAdmin

# 6. Generate encryption key (if not already done)
php spark key:generate

# 7. Clear cache
php spark cache:clear
```

## Verification Steps

After setup, verify everything works:

### 1. Check Database Tables
```sql
SHOW TABLES;
```
Should show: users, payers, contributions, payments, payment_methods, etc.

### 2. Check Payment Methods
```sql
SELECT name, status FROM payment_methods WHERE status = 'active';
```
Should show at least 4 active payment methods.

### 3. Check Users
```sql
SELECT username, email FROM users;
```
Should show at least the admin user.

### 4. Test Payment Creation
1. Login as admin
2. Go to Payments > Add Payment
3. Select existing payer
4. Select contribution
5. **Verify payment method dropdown has options** ← Critical check!
6. Select payment method
7. Enter amount
8. Save payment

If payment method dropdown is empty, payment methods weren't seeded!

## Common Issues and Solutions

### Issue: Payment method dropdown is empty
**Solution:**
```bash
php spark db:seed PaymentMethodSeeder
```

### Issue: Validation fails with "Invalid payment method"
**Solution:**
1. Check if payment methods exist: `SELECT * FROM payment_methods;`
2. If empty, run: `php spark db:seed PaymentMethodSeeder`
3. Clear browser cache
4. Try again

### Issue: "Payer not found" error
**Solution:**
1. Check if payers table exists: `SHOW TABLES LIKE 'payers';`
2. If missing, run: `php spark migrate`
3. Check if payers exist: `SELECT * FROM payers;`

### Issue: Database connection error
**Solution:**
1. Verify MySQL is running in XAMPP
2. Check database credentials in `app/Config/Database.php`
3. Verify database `clearpaydb` exists
4. Check `.env` file has correct database settings

## Automated Setup Script

Create a file `setup.bat` (Windows) or `setup.sh` (Linux/Mac) to automate setup:

### Windows (setup.bat)
```batch
@echo off
echo Setting up ClearPay...
echo.

echo Step 1: Installing dependencies...
call composer install
echo.

echo Step 2: Running migrations...
php spark migrate
echo.

echo Step 3: Seeding database...
php spark db:seed DatabaseSeeder
echo.

echo Step 4: Generating encryption key...
php spark key:generate
echo.

echo Step 5: Clearing cache...
php spark cache:clear
echo.

echo Setup complete!
echo.
echo Please verify:
echo 1. Payment methods exist in database
echo 2. Admin user exists
echo 3. Database tables are created
pause
```

### Linux/Mac (setup.sh)
```bash
#!/bin/bash
echo "Setting up ClearPay..."
echo

echo "Step 1: Installing dependencies..."
composer install
echo

echo "Step 2: Running migrations..."
php spark migrate
echo

echo "Step 3: Seeding database..."
php spark db:seed DatabaseSeeder
echo

echo "Step 4: Generating encryption key..."
php spark key:generate
echo

echo "Step 5: Clearing cache..."
php spark cache:clear
echo

echo "Setup complete!"
echo
echo "Please verify:"
echo "1. Payment methods exist in database"
echo "2. Admin user exists"
echo "3. Database tables are created"
```

## What's Different Between Devices?

### Working Device (Your Computer)
- ✅ Migrations run
- ✅ Seeders run (including PaymentMethodSeeder)
- ✅ Payment methods exist in database
- ✅ All dependencies installed
- ✅ Environment configured

### Non-Working Device (After Pull)
- ❌ Migrations might not be run
- ❌ Seeders might not be run (especially PaymentMethodSeeder)
- ❌ Payment methods don't exist in database
- ❌ Dependencies might not be installed
- ❌ Environment might not be configured

## The Root Cause

**The main issue:** Payment methods are not automatically created when you pull from GitHub. They must be seeded using:
```bash
php spark db:seed PaymentMethodSeeder
```

Or via the DatabaseSeeder:
```bash
php spark db:seed DatabaseSeeder
```

**Why this happens:**
- Migrations create table structure
- Seeders populate initial data
- Payment methods are **data**, not structure
- Data is not stored in Git (only code is)
- Therefore, payment methods must be seeded on each device

## Prevention

To prevent this issue, always:
1. Document setup steps in README
2. Create setup scripts
3. Add verification checks
4. Include seeders in setup documentation
5. Test setup on clean environment

