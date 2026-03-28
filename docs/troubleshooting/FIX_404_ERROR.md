# Fix 404 Error - "Can't find a route for 'GET: ClearPay/public'"

## The Problem
CodeIgniter is receiving "ClearPay/public" as a route instead of the root route "/". This means mod_rewrite isn't working properly.

## Solutions

### Solution 1: Enable mod_rewrite in Apache (Most Common Fix)

1. **Open Apache configuration:**
   - In XAMPP Control Panel, click "Config" next to Apache
   - Select "httpd.conf"

2. **Find and uncomment mod_rewrite:**
   - Search for: `#LoadModule rewrite_module modules/mod_rewrite.so`
   - Remove the `#` to uncomment:
     ```apache
     LoadModule rewrite_module modules/mod_rewrite.so
     ```

3. **Find and update AllowOverride:**
   - Search for: `<Directory "C:/xampp/htdocs">`
   - Find: `AllowOverride None`
   - Change to: `AllowOverride All`
   - Should look like:
     ```apache
     <Directory "C:/xampp/htdocs">
         Options Indexes FollowSymLinks
         AllowOverride All
         Require all granted
     </Directory>
     ```

4. **Save and restart Apache:**
   - Save httpd.conf
   - In XAMPP Control Panel, stop and start Apache

5. **Test again:**
   - Go to: `http://localhost/ClearPay/public/`
   - Should now work!

### Solution 2: Use PHP Built-in Server (Quick Alternative)

If mod_rewrite still doesn't work, use PHP's built-in server:

1. **Run the server:**
   ```cmd
   cd C:\xampp\htdocs\ClearPay\public
   php -S localhost:8000
   ```

2. **Access:**
   - Go to: `http://localhost:8000/`
   - This bypasses Apache and mod_rewrite entirely

### Solution 3: Check .htaccess File Location

Make sure `.htaccess` is in the `public` folder:
- Location: `C:\xampp\htdocs\ClearPay\public\.htaccess`
- Should exist and have the rewrite rules

### Solution 4: Verify RewriteBase

The `.htaccess` file should have:
```apache
RewriteBase /ClearPay/public/
```

This has already been updated in your project.

## Quick Test

After enabling mod_rewrite, test if it's working:

1. Create a test file: `C:\xampp\htdocs\test-rewrite.php`
   ```php
   <?php
   echo "mod_rewrite test";
   ```

2. Access: `http://localhost/test-rewrite.php`
   - Should show the content

3. Access: `http://localhost/test-rewrite` (without .php)
   - If mod_rewrite works, should still show content
   - If not, will show 404

## Most Likely Fix

**Enable mod_rewrite in Apache httpd.conf** - This is the #1 cause of this error!

