# Payment History Error Fix

## 🔴 Problem

When clicking on a payment in the Payments page, you get the error:
**"An error occurred while fetching payment history."**

## 🔍 Root Cause

**BaseURL Mismatch**: Your site is accessed at `https://clearpay.infinityfreeapp.com/` but your configuration is set to `https://clearpay.fwh.is/`.

When JavaScript calls `base_url('payments/get-payment-history')`, it generates:
- ❌ `https://clearpay.fwh.is/payments/get-payment-history` (wrong domain)
- ✅ Should be: `https://clearpay.infinityfreeapp.com/payments/get-payment-history`

This causes:
1. **CORS errors** (browser blocks cross-origin requests)
2. **404 errors** (request goes to wrong domain)
3. **Network failures** (domain doesn't exist or isn't accessible)

## ✅ Solution

Update the `baseURL` in both `.env` file and `app/Config/App.php` to match your actual domain.

### Option 1: Use InfinityFree App Domain (Current)

If you want to keep using `clearpay.infinityfreeapp.com`:

#### Step 1: Update `.env` file

Change this line in your `.env` file:
```env
# OLD (wrong)
app.baseURL = https://clearpay.fwh.is/

# NEW (correct)
app.baseURL = https://clearpay.infinityfreeapp.com/
```

#### Step 2: Update `app/Config/App.php`

Change line 19:
```php
// OLD (wrong)
public string $baseURL = 'https://clearpay.fwh.is/';

// NEW (correct)
public string $baseURL = 'https://clearpay.infinityfreeapp.com/';
```

#### Step 3: Update CORS (Optional but Recommended)

Add the InfinityFree domain to `app/Config/Cors.php`:

```php
'allowedOrigins' => [
    'https://clearpay.infinityfreeapp.com',  // Add this
    'https://clearpay.fwh.is',               // Keep if you'll use it later
    'http://localhost',
    // ... rest of origins
],

'allowedOriginsPatterns' => [
    'https://clearpay\.infinityfreeapp\.com',  // Add this
    'https://clearpay\.fwh\.is',               // Keep if you'll use it later
    // ... rest of patterns
],
```

### Option 2: Use Custom Domain (fwh.is)

If you want to use `clearpay.fwh.is` instead:

1. **Set up DNS** for `clearpay.fwh.is` to point to InfinityFree
2. **Add domain** in InfinityFree control panel
3. **Wait for DNS propagation** (24-48 hours)
4. **Access site** at `https://clearpay.fwh.is/`
5. **Keep current config** (already set to fwh.is)

---

## 🚀 Quick Fix Steps

1. **Edit `.env` file** on your InfinityFree server:
   ```
   app.baseURL = https://clearpay.infinityfreeapp.com/
   ```

2. **Edit `app/Config/App.php`** (upload updated file):
   ```php
   public string $baseURL = 'https://clearpay.infinityfreeapp.com/';
   ```

3. **Clear cache** (if using CodeIgniter cache):
   - Delete files in `writable/cache/`
   - Or run: `php spark cache:clear` (if SSH available)

4. **Test**: 
   - Refresh the Payments page
   - Click on a payment
   - Payment history should load correctly ✅

---

## 🔧 Additional Checks

### Check 1: Verify Route Exists

The route should be defined in `app/Config/Routes.php`:
```php
$routes->get('/payments/get-payment-history', 'Admin\PaymentsController::getPaymentHistory', ['filter' => 'auth']);
```
✅ This is already correct (line 135)

### Check 2: Verify Controller Method

The controller method exists in `app/Controllers/Admin/PaymentsController.php`:
```php
public function getPaymentHistory()
```
✅ This is already correct (line 69)

### Check 3: Check Browser Console

After making the fix, open browser Developer Tools (F12) → Console tab, and check for:
- ❌ CORS errors
- ❌ 404 errors
- ❌ Network failures

If you still see errors, check the Network tab to see what URL is being requested.

---

## 📝 Summary

**The Problem**: BaseURL mismatch between actual domain and configuration  
**The Fix**: Update `baseURL` in `.env` and `app/Config/App.php` to match your actual domain  
**The Result**: AJAX requests will work correctly ✅

---

## ⚠️ Important Notes

1. **Both files must match**: `.env` and `app/Config/App.php` should have the same baseURL
2. **Trailing slash required**: Always end with `/` (e.g., `https://domain.com/`)
3. **HTTPS required**: Use `https://` for production (not `http://`)
4. **Clear cache**: After changing baseURL, clear browser cache or hard refresh (Ctrl+F5)

---

## 🐛 Still Not Working?

If the error persists after fixing baseURL:

1. **Check browser console** (F12) for specific error messages
2. **Check Network tab** to see the actual request URL
3. **Check server logs** in `writable/logs/` for PHP errors
4. **Verify authentication**: Make sure you're logged in (route requires `auth` filter)
5. **Check database connection**: Ensure `.env` database credentials are correct

---

**After applying this fix, the payment history should load correctly!** ✅

