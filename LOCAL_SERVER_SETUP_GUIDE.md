# Local Server Setup Guide for ClearPay

This guide will help you set up your extra PC as a server for ClearPay, avoiding InfinityFree hosting conflicts.

## Prerequisites
- Extra PC with Windows installed
- XAMPP (or similar) installed on the extra PC
- Both PCs on the same network (WiFi or Ethernet)

## Step 1: Set Up XAMPP on Extra PC

1. **Install XAMPP** (if not already installed)
   - Download from: https://www.apachefriends.org/
   - Install to default location: `C:\xampp`

2. **Copy Your ClearPay Project**
   - Copy the entire `ClearPay` folder to: `C:\xampp\htdocs\ClearPay`
   - Or use the same structure you have now

3. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL**
   - Make sure both show green "Running" status

## Step 2: Configure CodeIgniter for Local Network

1. **Find Your Server PC's IP Address**
   - On the extra PC, open Command Prompt
   - Type: `ipconfig`
   - Look for "IPv4 Address" (e.g., `192.168.1.100`)
   - **Write this down!** This is your server IP

2. **Update `.env` File on Server PC**
   ```env
   CI_ENVIRONMENT = development
   app.baseURL = 'http://192.168.1.100/ClearPay/public/'
   ```
   Replace `192.168.1.100` with your actual server IP

3. **Update `app/Config/App.php`**
   ```php
   public string $baseURL = 'http://192.168.1.100/ClearPay/public/';
   ```
   Replace `192.168.1.100` with your actual server IP

4. **Configure Database**
   - Make sure MySQL is running
   - Import your database if needed
   - Update `.env` database settings:
   ```env
   database.default.hostname = localhost
   database.default.database = your_database_name
   database.default.username = root
   database.default.password = 
   database.default.DBPrefix = 
   ```

## Step 3: Configure Windows Firewall

1. **Allow Apache Through Firewall**
   - Open Windows Defender Firewall
   - Click "Allow an app through firewall"
   - Find "Apache HTTP Server" and check both Private and Public
   - If not listed, click "Allow another app" and browse to `C:\xampp\apache\bin\httpd.exe`

2. **Allow Port 80 (HTTP)**
   - In Firewall, go to "Advanced settings"
   - Click "Inbound Rules" → "New Rule"
   - Select "Port" → Next
   - Select "TCP" and enter port "80"
   - Allow the connection
   - Name it "Apache HTTP Server"

## Step 4: Update Flutter App

1. **Update `flutter_app/lib/services/api_service.dart`**
   - Change `baseUrl` to point to your server IP:
   ```dart
   static String get baseUrl {
     if (kIsWeb) {
       return 'http://192.168.1.100/ClearPay/public'; // Your server IP
     } else {
       // For mobile - use your server IP
       return 'http://192.168.1.100/ClearPay/public'; // Your server IP
       // For Android Emulator, use: 'http://10.0.2.2/ClearPay/public'
     }
   }
   ```

2. **For Android Emulator Testing**
   - If testing on Android emulator, use: `http://10.0.2.2/ClearPay/public`
   - This is a special IP that maps to your host PC's localhost

3. **For Physical Device Testing**
   - Use your server PC's actual IP: `http://192.168.1.100/ClearPay/public`
   - Make sure your phone is on the same WiFi network

## Step 5: Test the Setup

1. **Test from Browser (on any PC)**
   - Open browser on your main PC
   - Go to: `http://192.168.1.100/ClearPay/public/`
   - You should see your ClearPay login page

2. **Test from Flutter App**
   - Build and run your Flutter app
   - Try logging in
   - Check console logs for connection status

## Step 6: Make Server IP Static (Optional but Recommended)

To prevent IP changes, set a static IP for your server PC:

1. **Open Network Settings**
   - Right-click network icon → "Open Network & Internet settings"
   - Click "Change adapter options"
   - Right-click your network adapter → "Properties"

2. **Configure Static IP**
   - Select "Internet Protocol Version 4 (TCP/IPv4)"
   - Click "Properties"
   - Select "Use the following IP address"
   - Enter:
     - IP: `192.168.1.100` (or similar, check your router's range)
     - Subnet: `255.255.255.0`
     - Gateway: Your router IP (usually `192.168.1.1`)
   - DNS: Use your router's DNS or `8.8.8.8` (Google)

## Troubleshooting

### Can't Access from Other Devices
- Check Windows Firewall settings
- Make sure both PCs are on same network
- Try disabling firewall temporarily to test
- Check Apache is running on server PC

### Connection Refused
- Verify server IP is correct
- Check Apache is listening on port 80
- Try accessing from server PC itself: `http://localhost/ClearPay/public/`

### Database Connection Issues
- Make sure MySQL is running
- Check database credentials in `.env`
- Verify database exists and has data

### Flutter App Can't Connect
- For Android Emulator: Use `10.0.2.2` instead of server IP
- For Physical Device: Use actual server IP, ensure same WiFi
- Check AndroidManifest.xml has INTERNET permission

## Benefits of This Setup

✅ No hosting restrictions
✅ Full control over server
✅ Faster development
✅ No CORS issues
✅ Easy debugging
✅ Free (no hosting costs)

## Security Notes

⚠️ This setup is for **local network only** (development/testing)
⚠️ For production, you'll need:
   - Proper domain name
   - SSL certificate (HTTPS)
   - Firewall rules
   - Regular backups
   - Security hardening

## Next Steps

Once everything works:
1. Test all Flutter app features
2. Set up automatic backups
3. Consider using a domain name (optional)
4. For production, consider proper hosting or VPS

