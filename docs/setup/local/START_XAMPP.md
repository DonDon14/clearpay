# How to Start XAMPP Apache

## Quick Steps:

1. **Open XAMPP Control Panel:**
   - Press `Win + R`
   - Type: `C:\xampp\xampp-control.exe`
   - Press Enter
   - Or search for "XAMPP Control Panel" in Start Menu

2. **Start Apache:**
   - In XAMPP Control Panel, find "Apache"
   - Click the "Start" button next to Apache
   - Wait for it to turn green (should show "Running")

3. **Verify Apache is Running:**
   - You should see "Running" in green next to Apache
   - Port should show "80" or "443"

4. **Access Your Application:**
   - Open browser
   - Go to: `http://localhost/ClearPay/public/`
   - You should see the ClearPay login page

## Troubleshooting:

### If Apache won't start:

**Port 80 is already in use:**
- Another application (like IIS, Skype, etc.) is using port 80
- Solution: Stop the other service or change Apache port

**To change Apache port:**
1. Click "Config" next to Apache in XAMPP
2. Select "httpd.conf"
3. Find `Listen 80` and change to `Listen 8080`
4. Find `ServerName localhost:80` and change to `ServerName localhost:8080`
5. Save and restart Apache
6. Access: `http://localhost:8080/ClearPay/public/`

**Firewall blocking:**
- Windows Firewall might be blocking Apache
- Solution: Allow Apache through firewall when prompted

**Apache service error:**
- Check XAMPP Control Panel logs (click "Logs" button)
- Common issues: Missing Visual C++ Redistributable, port conflicts

## Alternative: Use PHP Built-in Server (No XAMPP needed)

If XAMPP Apache won't start, you can use PHP's built-in server:

```cmd
cd C:\xampp\htdocs\ClearPay\public
php -S localhost:8000
```

Then access: `http://localhost:8000/`

---

## Quick Test:

After starting Apache, test if it's working:
- Open: `http://localhost/`
- You should see XAMPP dashboard or directory listing

Then access ClearPay:
- Open: `http://localhost/ClearPay/public/`
- You should see ClearPay login page

