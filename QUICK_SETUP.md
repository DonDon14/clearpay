# Quick Setup Guide - After Pulling from GitHub

## ⚠️ CRITICAL: These Steps MUST Be Done on Every New Device

After pulling from GitHub, the application **WILL NOT WORK** until you complete these steps:

### Step 1: Install Dependencies
```bash
composer install
```

### Step 2: Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database: `clearpaydb`
3. Collation: `utf8mb4_general_ci`

### Step 3: Run Migrations (Creates Tables)
```bash
php spark migrate
```

### Step 4: Seed Database (Populates Data) - **CRITICAL!**
```bash
php spark db:seed DatabaseSeeder
```

**This step is CRITICAL because it:**
- Creates the admin user
- Creates sample contributions
- **Creates payment methods (GCash, PayMaya, Bank Transfer, Cash, etc.)** ← **Required for payment validation!**

### Step 5: Verify Setup
```bash
php spark setup:verify
```

This will check:
- ✓ Database connection
- ✓ All tables exist
- ✓ Payment methods exist (at least 4)
- ✓ Users exist
- ✓ Environment configuration

### Step 6: Generate Encryption Key (if needed)
```bash
php spark key:generate
```

## Why Payment Creation Fails Without Setup

### The Problem:
1. **Migrations** create table structure (empty tables)
2. **Seeders** populate initial data (payment methods, users, etc.)
3. **Payment validation** checks if payment methods exist in database
4. **If payment methods don't exist** → Validation fails with "Invalid payment method"

### The Solution:
**Always run seeders after migrations:**
```bash
php spark db:seed DatabaseSeeder
```

This ensures:
- Payment methods are created
- Admin user is created
- Sample data is available

## Automated Setup (Windows)

Run the setup script:
```bash
setup.bat
```

This automatically:
1. Installs dependencies
2. Runs migrations
3. Seeds database
4. Verifies payment methods
5. Generates encryption key
6. Clears cache

## Manual Verification

After setup, verify payment methods exist:
```sql
SELECT name, status FROM payment_methods WHERE status = 'active';
```

Should show:
- GCash
- PayMaya
- Bank Transfer
- Cash
- (and possibly more)

## Common Error Messages

### "Invalid payment method"
**Cause:** Payment methods not seeded
**Fix:** `php spark db:seed PaymentMethodSeeder`

### "Validation failed"
**Cause:** Payment methods don't exist OR payer_id not set
**Fix:** 
1. Run: `php spark db:seed PaymentMethodSeeder`
2. Check browser console for payer_id value
3. Ensure you selected an existing payer

### "Payer not found"
**Cause:** Payer doesn't exist OR payer_id not being sent correctly
**Fix:**
1. Check if payer exists in database
2. Check browser console for payer_id value
3. Ensure you selected an existing payer from dropdown

## What's Different Between Devices?

| Your Working Device | New Device (After Pull) |
|---------------------|-------------------------|
| ✅ Migrations run | ❌ Migrations not run |
| ✅ Seeders run | ❌ Seeders not run |
| ✅ Payment methods exist | ❌ Payment methods don't exist |
| ✅ Users exist | ❌ Users don't exist |
| ✅ Dependencies installed | ❌ Dependencies not installed |
| ✅ Environment configured | ❌ Environment not configured |

## The Fix

**Always run these commands after pulling:**
```bash
composer install
php spark migrate
php spark db:seed DatabaseSeeder
php spark setup:verify
```

This ensures 100% working setup on any device!

