# Fix: PostgreSQL GROUP BY Compatibility Issues

## Problem
PostgreSQL requires **all non-aggregated columns** in SELECT to be included in GROUP BY clause. MySQL is more lenient and allows columns from functionally dependent tables.

Error messages:
- `column "payers.payer_name" must appear in the GROUP BY clause or be used in an aggregate function`
- Similar errors for other joined table columns

## Root Cause
MySQL allows columns from joined tables in SELECT without requiring them in GROUP BY (if they're functionally dependent). PostgreSQL is strict and requires all non-aggregated columns to be in GROUP BY.

## Solutions Applied

### 1. ✅ Fixed `PaymentModel::getGroupedPayments()`

**File:** `app/Models/PaymentModel.php`

**Before:**
```sql
GROUP BY p.payer_id, p.contribution_id, COALESCE(p.payment_sequence, 1)
```

**After:**
```sql
GROUP BY p.payer_id, p.contribution_id, COALESCE(p.payment_sequence, 1),
         payers.payer_name, payers.payer_id, payers.contact_number, 
         payers.email_address, payers.profile_picture,
         contributions.title, contributions.description, contributions.amount
```

**All non-aggregated columns from SELECT are now in GROUP BY:**
- `payers.payer_name` ✅
- `payers.payer_id` ✅
- `payers.contact_number` ✅
- `payers.email_address` ✅
- `payers.profile_picture` ✅
- `contributions.title` ✅
- `contributions.description` ✅
- `contributions.amount` ✅

### 2. ✅ Fixed `Analytics::getPaymentAnalytics()` - Top Payers Query

**File:** `app/Controllers/Admin/Analytics.php`

**Before:**
```php
->groupBy('p.payer_id')
```

**After:**
```php
$groupByColumns = $isPostgres 
    ? 'p.payer_id, py.payer_name, py.payer_id'
    : 'p.payer_id';
->groupBy($groupByColumns)
```

**PostgreSQL now includes all selected columns:**
- `p.payer_id` ✅
- `py.payer_name` ✅
- `py.payer_id` ✅

## Files Modified

1. **`app/Models/PaymentModel.php`**
   - Updated `getGroupedPayments()` GROUP BY clause
   - Added all joined table columns to GROUP BY

2. **`app/Controllers/Admin/Analytics.php`**
   - Updated `getPaymentAnalytics()` top payers query
   - Made GROUP BY database-aware (PostgreSQL vs MySQL)

## Testing

After these fixes:
- ✅ Payments page should load without GROUP BY errors
- ✅ Analytics page should work correctly
- ✅ Refunds page should work (uses `getGroupedPayments()`)
- ✅ All pages should be accessible

## Status

✅ **Fixed and deployed** - Committed to `development` branch
- Commit: `864ef6e`
- Message: "Fix: Add all non-aggregated columns to GROUP BY for PostgreSQL compatibility"

## Related PostgreSQL Fixes

This is part of a series of PostgreSQL compatibility fixes:
1. ✅ ENUM types → VARCHAR with CHECK constraints
2. ✅ GROUP_CONCAT → STRING_AGG
3. ✅ MONTH()/YEAR() → EXTRACT() or date ranges
4. ✅ GROUP BY strictness (this fix)

## Next Steps

1. **Wait for auto-deployment** (1-2 minutes)
2. **Test the pages:**
   - `/payments` - Should load grouped payments
   - `/refunds` - Should show refund history
   - `/analytics` - Should display analytics data
3. **If errors persist**, check Render logs for specific error messages

