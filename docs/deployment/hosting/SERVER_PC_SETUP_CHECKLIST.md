# Server PC Setup Checklist

Your server PC IP is: **192.168.18.2**

## ✅ Step 1: Copy Files to Server PC

1. Copy your entire `ClearPay` folder to: `C:\xampp\htdocs\ClearPay` on the server PC
2. Make sure the folder structure is: `C:\xampp\htdocs\ClearPay\public\index.php`

## ✅ Step 2: Update Backend Configuration on Server PC

### Update `.env` file on server PC:
```env
CI_ENVIRONMENT = development
app.baseURL = 'http://192.168.18.2/ClearPay/public/'
```

### Update `app/Config/App.php` on server PC:
```php
public string $baseURL = 'http://192.168.18.2/ClearPay/public/';
```

### Update Database Settings in `.env` (if needed):
```env
database.default.hostname = localhost
database.default.database = clearpaydb
database.default.username = root
database.default.password = 
database.default.DBPrefix = 
```

## ✅ Step 3: Configure Flutter App

### For Android Emulator Testing:
- Use: `http://10.0.2.2/ClearPay/public`
- This is already set in `api_service.dart` line 30

### For Physical Device Testing:
- Change line 30 in `flutter_app/lib/services/api_service.dart`:
  ```dart
  return 'http://192.168.18.2/ClearPay/public'; // Physical Device
  ```
- Comment out the emulator line (line 30)
- Make sure your phone is on the same WiFi network as the server PC

## ✅ Step 4: Test from Browser First

1. On your main PC, open browser
2. Go to: `http://192.168.18.2/ClearPay/public/`
3. You should see the ClearPay login page
4. If you see XAMPP dashboard instead, the path is wrong - check folder structure

## ✅ Step 5: Configure Windows Firewall on Server PC

1. Open Windows Defender Firewall
2. Click "Allow an app through firewall"
3. Find "Apache HTTP Server" and check both Private and Public
4. If not listed, browse to: `C:\xampp\apache\bin\httpd.exe`

## ✅ Step 6: Test Flutter App

1. Rebuild your Flutter app: `flutter clean && flutter pub get && flutter build apk`
2. Install on device/emulator
3. Try logging in
4. Check console logs for errors

## Troubleshooting

### "Connection refused" or "Cannot connect"
- ✅ Check Apache is running on server PC (XAMPP Control Panel)
- ✅ Check Windows Firewall allows Apache
- ✅ Verify server IP is correct: `192.168.18.2`
- ✅ Make sure both devices are on same WiFi network

### "404 Not Found"
- ✅ Check folder structure: `C:\xampp\htdocs\ClearPay\public\index.php` exists
- ✅ Try accessing: `http://192.168.18.2/ClearPay/public/` from browser
- ✅ Check `.htaccess` file exists in `public` folder

### "Database connection error"
- ✅ Check MySQL is running on server PC
- ✅ Verify database exists and has data
- ✅ Check database credentials in `.env`

### Flutter app shows "Server error: 404"
- ✅ Make sure you're using the correct endpoint: `/api/payer/login`
- ✅ Check the route exists in `app/Config/Routes.php`
- ✅ Verify `baseURL` in Flutter app matches server IP

