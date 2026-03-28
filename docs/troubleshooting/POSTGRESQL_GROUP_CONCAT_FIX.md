# Fix: GROUP_CONCAT PostgreSQL Compatibility

## Problem
The application was using MySQL's `GROUP_CONCAT()` function which doesn't exist in PostgreSQL, causing 500 errors on pages that use grouped payment queries.

## Error Message
```
pg_query(): Query failed: ERROR: function group_concat(integer) does not exist
LINE 24: GROUP_CONCAT(DISTINCT p.id) as payment_ids
```

## Solution
Updated `app/Models/PaymentModel.php` to detect the database type and use the appropriate aggregation function:

- **MySQL**: `GROUP_CONCAT(DISTINCT p.id)`
- **PostgreSQL**: `STRING_AGG(DISTINCT p.id::text, ',')`

## Changes Made

### File: `app/Models/PaymentModel.php`

**Before:**
```php
GROUP_CONCAT(DISTINCT p.id) as payment_ids
```

**After:**
```php
// Detect database type
$dbDriver = $db->getPlatform();
$isPostgres = (strpos(strtolower($dbDriver), 'postgre') !== false);

// Use database-appropriate function
if ($isPostgres) {
    $concatFunction = "STRING_AGG(DISTINCT p.id::text, ',')";
} else {
    $concatFunction = "GROUP_CONCAT(DISTINCT p.id)";
}

// Use in query
{$concatFunction} as payment_ids
```

## Technical Details

### PostgreSQL STRING_AGG
- Syntax: `STRING_AGG(expression, delimiter)`
- For DISTINCT: `STRING_AGG(DISTINCT expression::text, delimiter)`
- Note: PostgreSQL requires explicit type casting (`::text`) for integers

### MySQL GROUP_CONCAT
- Syntax: `GROUP_CONCAT(DISTINCT expression)`
- Default delimiter: comma (`,`)
- No type casting needed

## Testing

After this fix:
- ✅ Works with PostgreSQL (Render.com)
- ✅ Still works with MySQL (local development)
- ✅ Database-agnostic solution
- ✅ No breaking changes

## Status

✅ **Fixed and deployed** - Committed to `development` branch
- Commit: `3bad706`
- Message: "Fix: Replace GROUP_CONCAT with STRING_AGG for PostgreSQL compatibility"

## Related Issues

This is similar to the previous `ENUM` type fix - both were MySQL-specific features that needed PostgreSQL equivalents.

