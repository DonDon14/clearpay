# Dynamic ngrok URL Guide

## Problem Solved

Previously, if ngrok restarted and got a new URL, you would need to:
1. Update the code
2. Rebuild the app
3. Reinstall it

**Now, the app can update the ngrok URL dynamically without rebuilding!**

## How It Works

The ngrok URL is now stored in `SharedPreferences` (device storage), which means:
- ✅ You can update it from within the app
- ✅ No code changes needed
- ✅ No rebuild required
- ✅ Works immediately after updating

## How to Update ngrok URL

### Method 1: Auto-Fetch (Recommended)

If ngrok is running on the same machine as your development server:

1. **Open the app**
2. **Go to Server Settings** (from navigation drawer → Server Settings)
3. **Click "Auto-Fetch from ngrok"** button
4. The app will automatically fetch the current ngrok URL from `http://127.0.0.1:4040/api/tunnels`
5. **Click "Save"**

### Method 2: Manual Entry

1. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

2. **Get your ngrok URL:**
   - Open http://127.0.0.1:4040 in your browser
   - Copy the "Forwarding" URL (e.g., `https://abc123.ngrok.io`)

3. **In the app:**
   - Go to **Server Settings** (from navigation drawer)
   - Enter: `https://abc123.ngrok.io/ClearPay/public`
   - Click **"Save"**

### Method 3: Clear ngrok URL (Use Local Network)

To switch back to local network IP:
1. Go to **Server Settings**
2. Clear the ngrok URL field (make it empty)
3. Click **"Save"**

The app will automatically use your local network IP.

## Accessing Server Settings

**From Navigation Drawer:**
1. Open the navigation drawer (hamburger menu)
2. Scroll to bottom
3. Tap **"Server Settings"**

## Technical Details

### Storage
- ngrok URL is stored in `SharedPreferences` with key `'ngrok_url'`
- Persists across app restarts
- Can be updated at runtime

### URL Priority
1. **If ngrok URL is set** → Uses ngrok URL for all platforms
2. **If ngrok URL is empty/null** → Uses local network IP based on platform:
   - Android Emulator: `http://10.0.2.2/ClearPay/public`
   - Physical Device: `http://192.168.18.2/ClearPay/public`
   - iOS Simulator: `http://localhost/ClearPay/public`
   - Web: `http://192.168.18.2/ClearPay/public`

### Auto-Fetch Feature

The app can automatically fetch the ngrok URL from ngrok's local API:
- **Endpoint:** `http://127.0.0.1:4040/api/tunnels`
- **Works when:** ngrok is running on the same machine
- **Limitation:** Only works if app can access `127.0.0.1` (same machine)

For mobile devices, you'll need to manually enter the URL.

## API Methods

The `ApiService` class now includes:

```dart
// Set ngrok URL
await ApiService.setNgrokUrl('https://abc123.ngrok.io/ClearPay/public');

// Get current ngrok URL
final url = await ApiService.getNgrokUrl();

// Auto-fetch from ngrok API
final url = await ApiService.fetchNgrokUrlFromApi();

// Get base URL (automatically uses ngrok if set)
final baseUrl = await ApiService.getBaseUrl();
```

## Benefits

1. **No Rebuild Required** - Update URL without rebuilding app
2. **Immediate Effect** - Changes apply immediately
3. **User-Friendly** - Easy UI to update settings
4. **Flexible** - Switch between ngrok and local network easily
5. **Auto-Detection** - Can auto-fetch from ngrok API

## Troubleshooting

### Auto-Fetch Not Working
- **Problem:** "Could not fetch ngrok URL"
- **Solution:** 
  - Make sure ngrok is running
  - Check if ngrok is accessible at http://127.0.0.1:4040
  - On mobile devices, auto-fetch won't work (use manual entry)

### URL Not Saving
- **Problem:** URL doesn't persist after app restart
- **Solution:** 
  - Make sure URL starts with `http://` or `https://`
  - Check app permissions for storage

### Still Using Old URL
- **Problem:** App still uses old ngrok URL
- **Solution:**
  - Clear the ngrok URL field and save
  - Restart the app
  - Or update with new URL

## Example Workflow

1. **ngrok restarts** → Gets new URL: `https://xyz789.ngrok.io`
2. **Open app** → Go to Server Settings
3. **Click "Auto-Fetch"** → App fetches new URL automatically
4. **Click "Save"** → URL is saved
5. **App immediately uses new URL** → No restart needed!

Or manually:
1. **Copy new URL** from ngrok dashboard
2. **Paste in app** → `https://xyz789.ngrok.io/ClearPay/public`
3. **Click "Save"** → Done!

Your app will now continue working even when ngrok changes its URL! 🎉

