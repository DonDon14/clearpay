# Flutter App Server Connection Guide

## Overview

Your Flutter app is now fully configured to fetch data from your ClearPay server. All API methods have been updated to include proper authentication headers and error handling.

## Configuration

### Base URL Setup

The API service automatically detects the platform and uses the appropriate base URL. You can configure it in `lib/services/api_service.dart`:

#### For Local Network Access:
```dart
static const String serverIp = '192.168.18.2';  // Your server's IP
static const String projectPath = '/ClearPay/public';
```

#### For External Access (ngrok):
```dart
static const String? ngrokUrl = 'https://abc123.ngrok.io/ClearPay/public';
```

**Note:** If `ngrokUrl` is set, it will be used for all platforms. Leave it as `null` to use local network IP.

### Platform-Specific URLs:

- **Android Emulator**: `http://10.0.2.2/ClearPay/public` (automatically mapped)
- **Physical Device**: `http://192.168.18.2/ClearPay/public` (use your server's IP)
- **iOS Simulator**: `http://localhost/ClearPay/public`
- **Flutter Web**: `http://192.168.18.2/ClearPay/public`

## Authentication

All authenticated API requests now automatically include the Bearer token in the `Authorization` header:

```dart
Authorization: Bearer <token>
```

The token is stored securely using `SharedPreferences` and is automatically included in all authenticated requests.

## Available API Endpoints

Your Flutter app can now fetch data from these endpoints:

### Authentication
- ✅ `POST /api/payer/login` - Login
- ✅ `POST /api/payer/signup` - Signup
- ✅ `POST /api/payer/verify-email` - Verify email
- ✅ `POST /api/payer/forgot-password` - Forgot password
- ✅ `POST /api/payer/reset-password` - Reset password

### Dashboard & Data
- ✅ `GET /api/payer/dashboard` - Dashboard data
- ✅ `GET /api/payer/contributions` - User contributions
- ✅ `GET /api/payer/payment-history` - Payment history
- ✅ `GET /api/payer/announcements` - Announcements
- ✅ `GET /api/payer/payment-requests` - Payment requests
- ✅ `GET /api/payer/refund-requests` - Refund requests

### Payment Methods
- ✅ `GET /api/payer/payment-methods` - Active payment methods
- ✅ `GET /api/payer/refund-methods` - Active refund methods
- ✅ `POST /api/payer/submit-payment-request` - Submit payment request
- ✅ `POST /api/payer/submit-refund-request` - Submit refund request

### Profile & Activities
- ✅ `POST /api/payer/update-profile` - Update profile
- ✅ `POST /api/payer/upload-profile-picture` - Upload profile picture
- ✅ `GET /api/payer/check-new-activities` - Check new notifications
- ✅ `GET /api/payer/get-all-activities` - Get all notifications
- ✅ `POST /api/payer/mark-activity-read/:id` - Mark notification as read

### Contribution Details
- ✅ `GET /api/payer/get-contribution-details` - Get contribution details
- ✅ `GET /api/payer/get-contribution-payments/:id` - Get contribution payments

## Testing the Connection

### 1. Check Server Status
Ensure your server is running:
- Apache is running
- MySQL is running
- Your ClearPay application is accessible at `http://192.168.18.2/ClearPay/public`

### 2. Test from Flutter App

1. **Login Test:**
   - Open the Flutter app
   - Try logging in with valid credentials
   - Check console for any connection errors

2. **Dashboard Test:**
   - After login, the dashboard should automatically load
   - Pull down to refresh if data doesn't appear

3. **Network Debugging:**
   - Check Flutter console for API request logs
   - Look for error messages in the app

### 3. Common Issues

#### Connection Timeout
- **Problem**: App can't connect to server
- **Solution**: 
  - Verify server IP is correct
  - Check if server is running
  - Ensure device/emulator is on same network
  - For physical device, use server's actual IP (not `10.0.2.2`)

#### Authentication Errors
- **Problem**: "Not authenticated" errors
- **Solution**:
  - Ensure login was successful
  - Check if token is being stored
  - Verify token is included in request headers

#### CORS Errors (Web only)
- **Problem**: CORS errors in browser console
- **Solution**:
  - Backend should handle CORS (already configured)
  - Check if server allows requests from your domain

## Using ngrok for External Access

If you want to access your app from outside your local network:

1. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

2. **Get your ngrok URL:**
   - Open http://127.0.0.1:4040
   - Copy the Forwarding URL (e.g., `https://abc123.ngrok.io`)

3. **Update Flutter app:**
   ```dart
   static const String? ngrokUrl = 'https://abc123.ngrok.io/ClearPay/public';
   ```

4. **Restart your Flutter app**

## API Service Features

### Automatic Authentication
All authenticated requests automatically include the Bearer token:
```dart
headers: _getHeaders()  // Includes Authorization header if token exists
```

### Error Handling
All API methods include comprehensive error handling:
- Connection timeouts
- Network errors
- Server errors
- Authentication errors

### Response Format
All API responses follow this format:
```json
{
  "success": true/false,
  "data": {...},
  "error": "error message" (if success is false)
}
```

## Next Steps

1. ✅ **Authentication** - Working
2. ✅ **Data Fetching** - All endpoints configured
3. ✅ **Error Handling** - Comprehensive error handling
4. ✅ **Token Management** - Automatic token inclusion

Your Flutter app is now fully functional and ready to fetch data from your server!

## Troubleshooting

### Check API Service Configuration
File: `lib/services/api_service.dart`

### Verify Server Endpoints
Check `app/Config/Routes.php` for available API endpoints

### Test API Directly
Use Postman or curl to test endpoints:
```bash
curl -X POST http://192.168.18.2/ClearPay/public/api/payer/login \
  -H "Content-Type: application/json" \
  -d '{"payer_id":"test","password":"test"}'
```

### Enable Debug Logging
The API service includes debug print statements. Check Flutter console for:
- Request URLs
- Response status codes
- Response bodies

