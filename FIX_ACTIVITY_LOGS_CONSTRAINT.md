# Fix Activity Logs Constraint Violation

## Problem
When approving or rejecting payment requests, the following error occurs:
```
ERROR: new row for relation "activity_logs" violates check constraint "activity_logs_action_check"
```

## Root Cause
The PostgreSQL check constraint `activity_logs_action_check` only allows:
- 'created', 'updated', 'deleted', 'published', 'unpublished'

But the code is trying to log:
- 'approved', 'rejected', 'processed', 'submitted'

## Solution
Created a new migration file to update the existing constraint on the database.

### Migration File
**File:** `app/Database/Migrations/2025-11-13-000001_UpdateActivityLogsConstraints.php`

This migration will:
1. Drop the existing `activity_logs_action_check` constraint
2. Recreate it with the updated allowed values:
   - 'created', 'updated', 'deleted', 'published', 'unpublished', 'approved', 'rejected', 'processed', 'submitted'
3. Also update `activity_logs_activity_type_check` to include:
   - 'payment_request', 'refund'

## Deployment

### Automatic (Render)
The migration will run automatically when you deploy because `docker-entrypoint.sh` runs:
```bash
php spark migrate
```

### Manual (if needed)
If the migration doesn't run automatically, you can run it manually:
```bash
php spark migrate
```

## Verification
After deployment, test:
1. ✅ Approve a payment request - should work without constraint error
2. ✅ Reject a payment request - should work without constraint error
3. ✅ Check activity logs - should show 'approved'/'rejected' actions

## Related Files
- `app/Database/Migrations/2025-11-13-000001_UpdateActivityLogsConstraints.php` - New migration
- `app/Database/Migrations/2025-01-01-000007_CreateActivityLogsTable.php` - Original migration (updated for future deployments)
- `app/Services/ActivityLogger.php` - Activity logging service
- `app/Controllers/Admin/DashboardController.php` - Payment request approval/rejection

## Notes
- The migration uses `DROP CONSTRAINT IF EXISTS` to safely handle existing constraints
- Error handling is included to prevent migration failure if constraints don't exist
- The migration is idempotent - safe to run multiple times

