# Troubleshooting Connection Issues

## Issues Fixed

### 1. Platform Detection
- ✅ Updated API service to detect Flutter Web vs Mobile
- ✅ For Web: Uses `http://localhost/ClearPay/public`
- ✅ For Mobile: Uses platform-specific URLs

### 2. CORS Configuration
- ✅ Enabled CORS filter globally
- ✅ Added allowed origins: `localhost:*`, `127.0.0.1:*`, `10.0.2.2:*`
- ✅ Added allowed headers: Content-Type, Accept, Authorization, etc.
- ✅ Added allowed methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
- ✅ Enabled credentials support

### 3. Error Handling
- ✅ Added timeout (10 seconds)
- ✅ Added debug logging
- ✅ Better error messages

## Testing the API

### Step 1: Verify Backend is Running
1. Open XAMPP Control Panel
2. Ensure Apache and MySQL are running
3. Check if ClearPay is accessible: `http://localhost/ClearPay/public/`

### Step 2: Test API Endpoint Directly
Open your browser and go to:
```
http://localhost/ClearPay/public/api/payer/login
```

You should see an error (method not allowed for GET), but that's expected - it means the endpoint exists.

### Step 3: Test with Postman/curl
```bash
curl -X POST http://localhost/ClearPay/public/api/payer/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"payer_id":"YOUR_STUDENT_ID","password":"YOUR_PASSWORD"}'
```

### Step 4: Check Flutter Web Console
1. Open browser DevTools (F12)
2. Check Console tab for errors
3. Check Network tab to see the actual request being made
4. Look for CORS errors (red messages)

## Common Issues

### Issue: Connection Timeout
**Cause:** Backend not running or wrong URL

**Solution:**
1. Verify XAMPP Apache is running
2. Check ClearPay is accessible at `http://localhost/ClearPay/public/`
3. Update `baseUrl` in `lib/services/api_service.dart` if needed

### Issue: CORS Error
**Cause:** CORS not configured properly

**Solution:**
1. Check `app/Config/Cors.php` has correct origins
2. Check `app/Config/Filters.php` has `'cors'` in globals
3. Restart Apache after making changes

### Issue: 404 Not Found
**Cause:** Route not registered

**Solution:**
1. Check `app/Config/Routes.php` has: `$routes->post('api/payer/login', ...)`
2. Clear CodeIgniter cache if needed

### Issue: 405 Method Not Allowed
**Cause:** Wrong HTTP method

**Solution:**
- Ensure you're using POST method
- Check the route accepts POST

## Manual URL Testing

If automatic detection doesn't work, you can manually set the URL in `lib/services/api_service.dart`:

```dart
static String get baseUrl {
  // For Flutter Web running on localhost:59244
  if (kIsWeb) {
    return 'http://localhost/ClearPay/public'; // Change if your backend is on different port
  }
  // ... rest of code
}
```

## Next Steps

1. Restart your Flutter app after making changes
2. Check browser console for detailed error messages
3. Verify the API endpoint works with Postman first
4. Then test from Flutter app

