# Migration and Seeding Guide - Fix for Payment Validation Errors

## Problem
After pulling from GitHub and running migrations on a new device, payment creation fails with "Validation failed" error. The console shows:
- Empty `payer_id` and `payer_name` fields
- Payment method validation failing with "NOT FOUND"

## Root Causes

### 1. Payment Methods Not Seeded
The `DatabaseSeeder` was missing the `PaymentMethodSeeder` call, so payment methods weren't being seeded during migration.

### 2. Payer ID Not Being Set Correctly
The form might not be properly setting the `payer_id` hidden field when an existing payer is selected.

## Solution

### Step 1: Update Database Seeder
The `DatabaseSeeder.php` has been updated to include `PaymentMethodSeeder`. After pulling from GitHub, you need to:

1. **Run the seeder manually** (if you've already migrated):
   ```bash
   php spark db:seed PaymentMethodSeeder
   ```

2. **Or re-run the full seeder**:
   ```bash
   php spark db:seed DatabaseSeeder
   ```

### Step 2: Verify Payment Methods Exist
Check if payment methods are in the database:
```sql
SELECT * FROM payment_methods;
```

You should see at least:
- GCash
- PayMaya
- Bank Transfer
- Cash
- Online Banking

### Step 3: Verify Payer Selection
When selecting an existing payer, ensure:
1. The payer is properly selected from the dropdown
2. The hidden field `existingPayerId` (with name `payer_id`) is set
3. The `window.selectedPayerId` variable is set

### Step 4: Clear Browser Cache
After pulling from GitHub, clear your browser cache to ensure the latest JavaScript files are loaded.

## Migration Steps for New Device

1. **Pull from GitHub**:
   ```bash
   git pull origin main
   ```

2. **Run migrations**:
   ```bash
   php spark migrate
   ```

3. **Run seeders**:
   ```bash
   php spark db:seed DatabaseSeeder
   ```

4. **Verify payment methods**:
   ```bash
   php spark db:seed PaymentMethodSeeder
   ```

5. **Clear cache** (if needed):
   ```bash
   php spark cache:clear
   ```

## Testing

After migration, test payment creation:
1. Go to Payments > Add Payment
2. Select "Existing Payer"
3. Search and select a payer
4. Select a contribution
5. Enter amount
6. Select payment method (should show GCash, PayMaya, etc.)
7. Set payment date
8. Click "Save Payment"

The payment should save successfully without validation errors.

## Troubleshooting

### If payment methods are still missing:
```bash
# Check if table exists
php spark db:table payment_methods

# Manually seed payment methods
php spark db:seed PaymentMethodSeeder
```

### If payer_id is still empty:
1. Check browser console for JavaScript errors
2. Verify the hidden field exists: `<input type="hidden" id="existingPayerId" name="payer_id">`
3. Check if `window.selectedPayerId` is set when payer is selected
4. Clear browser cache and reload

### If validation still fails:
1. Check server logs: `writable/logs/log-YYYY-MM-DD.log`
2. Look for validation error details
3. Verify all required fields are being sent in the form data

