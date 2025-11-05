# Migration Guide

## Overview

This project uses **one migration per table** to avoid conflicts when pulling from GitHub. All migrations are consolidated into single files that create complete table structures.

## Migration Files

All migrations are located in `app/Database/Migrations/` and follow this naming convention:
- Format: `YYYY-MM-DD-HHMMSS_TableName.php`
- Current migrations use: `2025-01-01-00000X_CreateTableName.php`

### Migration Order

Migrations are ordered to respect foreign key dependencies:

1. **2025-01-01-000001_CreateUsersTable.php** - Base users table
2. **2025-01-01-000002_CreateUserActivitiesTable.php** - Depends on users
3. **2025-01-01-000003_CreateContributionsTable.php** - Depends on users
4. **2025-01-01-000004_CreatePayersTable.php** - Independent payer table
5. **2025-01-01-000005_CreatePaymentsTable.php** - Depends on payers, contributions, users
6. **2025-01-01-000006_CreateAnnouncementsTable.php** - Depends on users
7. **2025-01-01-000007_CreateActivityLogsTable.php** - Activity logs
8. **2025-01-01-000008_CreateActivityReadStatusTable.php** - Depends on activity_logs, payers
9. **2025-01-01-000009_CreateAdminReadStatusTable.php** - Depends on activity_logs, users
10. **2025-01-01-000010_CreatePaymentRequestsTable.php** - Depends on payers, contributions, users
11. **2025-01-01-000011_CreatePaymentMethodsTable.php** - Independent payment methods
12. **2025-01-01-000012_CreateRefundsTable.php** - Depends on payments, payers, contributions, users
13. **2025-01-01-000013_CreateRefundMethodsTable.php** - Independent refund methods (includes seed data)
14. **2025-01-01-000014_CreateContributionCategoriesTable.php** - Independent categories (includes seed data)
15. **2025-01-01-000015_CreateAuthTokensTable.php** - Depends on users

## Running Migrations

### Fresh Installation

When pulling the project for the first time or setting up a new environment:

```bash
# Run all migrations
php spark migrate

# Or run with a specific group
php spark migrate -g default
```

### Existing Database

If you already have a database with data:

1. **Backup your database first!**
2. Clear the migrations table:
   ```sql
   TRUNCATE TABLE migrations;
   ```
3. Run migrations:
   ```bash
   php spark migrate
   ```

### Rollback (if needed)

```bash
# Rollback all migrations
php spark migrate:rollback

# Rollback specific number of batches
php spark migrate:rollback -b 1
```

## Important Notes

### ✅ Benefits of This Approach

- **No conflicts**: Each table has one migration file
- **Clean structure**: Easy to understand what each table contains
- **Consistent**: Same structure across all environments
- **Git-friendly**: No merge conflicts from migration timestamps

### ⚠️ Important Warnings

1. **Never modify existing migrations** once they're in production
2. **Always create new migrations** for schema changes
3. **Test migrations** on a development database first
4. **Backup your database** before running migrations

### 🔄 Adding New Tables

When you need to add a new table:

1. Create a new migration file with the next sequential number:
   ```bash
   php spark make:migration CreateNewTableName
   ```

2. Rename the file to follow the convention:
   - Format: `2025-01-01-000016_CreateNewTableName.php`
   - Use the next available number (000016, 000017, etc.)

3. Ensure the class name matches the file name:
   ```php
   class CreateNewTableName extends Migration
   ```

### 📝 Migration Best Practices

1. **Include all fields** in the initial migration - don't add columns later unless necessary
2. **Use proper foreign keys** - they ensure data integrity
3. **Add indexes** for frequently queried columns
4. **Include seed data** in the migration if it's reference data (like payment methods, categories)
5. **Test the down() method** - ensure rollback works correctly

## Troubleshooting

### Migration Conflicts

If you encounter conflicts:
1. Check the `migrations` table in your database
2. Ensure all team members have the latest migration files
3. Clear the migrations table and re-run if on a fresh database

### Foreign Key Errors

If you get foreign key constraint errors:
1. Check the migration order
2. Ensure dependent tables are created first
3. Verify foreign key references are correct

### Migration Already Run

If a migration shows as already run but the table doesn't exist:
1. Check the `migrations` table
2. Manually remove the migration record if needed
3. Re-run the migration

## Database Schema Reference

For a complete overview of all tables and their relationships, refer to the migration files in `app/Database/Migrations/`.

