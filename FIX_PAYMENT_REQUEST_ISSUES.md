# Fix Payment Request Approval/Rejection Issues

## Issues Fixed

### 1. PostgreSQL Boolean Type Mismatch ✅
**Error:** `column "is_partial_payment" is of type boolean but expression is of type integer`

**Root Cause:** Code was using integers (0/1) instead of booleans (true/false) for PostgreSQL boolean columns.

**Files Fixed:**
- `app/Controllers/Admin/DashboardController.php` - Line 837
- `app/Controllers/Admin/PaymentsController.php` - Lines 399, 1127, 1251, 1256, 2289

**Changes:**
- Changed `$isPartial ? 1 : 0` → `$isPartial ? true : false`
- Changed `$updateData['is_partial_payment'] = 1` → `$updateData['is_partial_payment'] = true`
- Changed `$updateData['is_partial_payment'] = 0` → `$updateData['is_partial_payment'] = false`

### 2. Activity Logs Check Constraint Violation ✅
**Error:** `new row for relation "activity_logs" violates check constraint "activity_logs_action_check"`

**Root Cause:** The check constraint only allowed: 'created', 'updated', 'deleted', 'published', 'unpublished', but the code was trying to log 'approved', 'rejected', 'processed', 'submitted'.

**Files Fixed:**
- `app/Database/Migrations/2025-01-01-000007_CreateActivityLogsTable.php`

**Changes:**
- Updated `activity_logs_action_check` constraint to include: 'approved', 'rejected', 'processed', 'submitted'
- Updated `activity_logs_activity_type_check` constraint to include: 'payment_request', 'refund'
- Added logic to drop existing constraints before recreating them (for existing databases)

## Deployment Notes

### For Existing Databases (Render)
The migration will automatically:
1. Drop existing constraints if they exist
2. Recreate them with the updated allowed values

### Testing
After deployment, test:
1. ✅ Approve a payment request - should work without boolean error
2. ✅ Reject a payment request - should work without constraint violation
3. ✅ Check activity logs - should show 'approved'/'rejected' actions

## Related Files Reviewed
- `app/Controllers/Admin/DashboardController.php` - Payment request approval/rejection
- `app/Controllers/Admin/PaymentsController.php` - Payment creation/updates
- `app/Services/ActivityLogger.php` - Activity logging service
- `app/Database/Migrations/2025-01-01-000007_CreateActivityLogsTable.php` - Activity logs table migration

All boolean fields now use proper boolean values (true/false) instead of integers (0/1) for PostgreSQL compatibility.

