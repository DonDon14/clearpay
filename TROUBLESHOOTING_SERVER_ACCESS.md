# Troubleshooting: XAMPP Dashboard Instead of ClearPay

## Problem
You're seeing the XAMPP dashboard at `https://192.168.18.2/dashboard/` instead of ClearPay.

## Issues to Check

### 1. **Wrong URL - You're using HTTPS and wrong path**
   - ❌ Wrong: `https://192.168.18.2/dashboard/`
   - ✅ Correct: `http://192.168.18.2/ClearPay/public/`
   
   **Fix:** Use HTTP (not HTTPS) and include `/ClearPay/public/` in the path

### 2. **Folder Structure on Server PC**
   Check if your folder structure on the server PC is correct:
   ```
   C:\xampp\htdocs\
   └── ClearPay\
       ├── app\
       ├── public\
       │   ├── index.php  ← This file must exist
       │   └── .htaccess  ← This file must exist
       ├── system\
       ├── writable\
       └── ... (other files)
   ```

   **Verify:**
   - Open File Explorer on server PC
   - Go to: `C:\xampp\htdocs\ClearPay\public\`
   - Make sure `index.php` exists there
   - Make sure `.htaccess` exists there

### 3. **Apache Virtual Host Configuration (Optional but Recommended)**
   If the above doesn't work, you may need to configure Apache to recognize the ClearPay directory.

## Step-by-Step Fix

### Step 1: Verify Folder Structure
1. On server PC, open File Explorer
2. Navigate to: `C:\xampp\htdocs\`
3. Check if `ClearPay` folder exists
4. Inside `ClearPay`, check if `public` folder exists
5. Inside `public`, verify `index.php` exists

### Step 2: Test the Correct URL
1. On your main PC, open browser
2. Go to: `http://192.168.18.2/ClearPay/public/` (HTTP, not HTTPS)
3. You should see ClearPay login page

### Step 3: If Still Not Working - Check .htaccess
1. On server PC, verify file exists: `C:\xampp\htdocs\ClearPay\public\.htaccess`
2. If missing, copy it from your main PC
3. Make sure it's not named `.htaccess.txt` (should be just `.htaccess`)

### Step 4: Check Apache Error Logs
1. On server PC, open XAMPP Control Panel
2. Click "Logs" button next to Apache
3. Look for any errors related to ClearPay
4. Common errors:
   - "File does not exist" → Wrong folder structure
   - "mod_rewrite not enabled" → Need to enable mod_rewrite

### Step 5: Enable mod_rewrite (If Needed)
1. On server PC, open: `C:\xampp\apache\conf\httpd.conf`
2. Find line: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remove the `#` to uncomment it: `LoadModule rewrite_module modules/mod_rewrite.so`
4. Save file
5. Restart Apache in XAMPP Control Panel

## Quick Test URLs

Try these URLs in order:

1. `http://192.168.18.2/ClearPay/public/` ← **This should work**
2. `http://192.168.18.2/ClearPay/public/index.php` ← Fallback
3. `http://192.168.18.2/ClearPay/` ← Should redirect to public
4. `http://localhost/ClearPay/public/` ← Test from server PC itself

## Common Mistakes

❌ Using HTTPS instead of HTTP
❌ Missing `/ClearPay/public/` in URL
❌ Wrong folder structure (files in wrong location)
❌ `.htaccess` file missing or incorrectly named
❌ mod_rewrite not enabled in Apache

## Expected Result

When you access `http://192.168.18.2/ClearPay/public/`, you should see:
- ClearPay login page (not XAMPP dashboard)
- URL in address bar: `http://192.168.18.2/ClearPay/public/`
- No errors

