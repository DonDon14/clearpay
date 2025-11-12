# Testing Dynamic ngrok URL Feature

## Quick Test Steps

### Test 1: Verify URL is Loaded on App Start

1. **Set a ngrok URL:**
   - Open app → Server Settings
   - Enter: `https://test123.ngrok.io/ClearPay/public`
   - Click Save
   - Close app completely

2. **Restart app:**
   - Open app again
   - Check console logs - should see the URL being used
   - Try to login - should use the saved ngrok URL

### Test 2: Update URL Without Rebuild

1. **Start with one URL:**
   - Set URL: `https://old123.ngrok.io/ClearPay/public`
   - Save it
   - Try to login (should work if server is accessible)

2. **Update to new URL:**
   - Go to Server Settings
   - Change to: `https://new456.ngrok.io/ClearPay/public`
   - Click Save
   - **Don't restart app**

3. **Test immediately:**
   - Try to login again
   - Should use new URL immediately
   - Check console - should show new URL

### Test 3: Auto-Fetch Feature

1. **Start ngrok:**
   ```bash
   ngrok http 80
   ```

2. **In app:**
   - Go to Server Settings
   - Click "Auto-Fetch from ngrok"
   - Should automatically populate the URL field
   - Click Save

3. **Verify:**
   - URL should be saved
   - Try to login - should work

### Test 4: Clear URL (Use Local Network)

1. **Set ngrok URL first:**
   - Enter any ngrok URL and save

2. **Clear it:**
   - Go to Server Settings
   - Clear the URL field (make it empty)
   - Click Save

3. **Verify:**
   - Should show message: "ngrok URL cleared"
   - App should use local network IP
   - Try to login - should use local IP

## Expected Behavior

### ✅ What Should Work:

1. **URL Persistence:**
   - URL saved in Server Settings persists after app restart
   - App uses saved URL on startup

2. **Immediate Update:**
   - When you update URL in Server Settings, it takes effect immediately
   - No app restart needed
   - Next API call uses new URL

3. **Cache Update:**
   - `_cachedNgrokUrl` is updated immediately when `setNgrokUrl()` is called
   - `baseUrl` getter uses cached value instantly

4. **Fallback:**
   - If no ngrok URL is set, uses local network IP
   - Platform-specific IPs work correctly

### ❌ What Might Not Work:

1. **Auto-Fetch on Mobile:**
   - Auto-fetch only works if app can access `127.0.0.1:4040`
   - On physical devices, this won't work (use manual entry)

2. **URL Format:**
   - Must include full path: `https://abc123.ngrok.io/ClearPay/public`
   - Not just: `https://abc123.ngrok.io`

## Debugging

### Check Console Logs

When URL is updated, you should see:
```
ngrok URL updated to: https://abc123.ngrok.io/ClearPay/public
```

When URL is cleared:
```
ngrok URL cleared, using local network IP
```

### Check API Calls

All API calls should log the URL being used:
```
Attempting login to: https://abc123.ngrok.io/ClearPay/public/api/payer/login
```

### Verify Storage

The URL is stored in SharedPreferences with key `'ngrok_url'`. You can verify this is being saved correctly.

## Common Issues

### Issue: URL Not Updating Immediately

**Symptom:** Changed URL but app still uses old URL

**Solution:**
- Make sure you clicked "Save" button
- Check console for error messages
- Try restarting app (though it should work without restart)

### Issue: Auto-Fetch Not Working

**Symptom:** "Could not fetch ngrok URL" error

**Solution:**
- Make sure ngrok is running
- Check if http://127.0.0.1:4040 is accessible
- On mobile devices, use manual entry instead

### Issue: App Still Uses Old URL After Restart

**Symptom:** Saved URL but app uses local IP

**Solution:**
- Check if URL was actually saved (look in Server Settings)
- Verify URL format is correct
- Check console logs for errors

## Verification Checklist

- [ ] URL can be set in Server Settings
- [ ] URL persists after app restart
- [ ] URL updates immediately without restart
- [ ] Auto-fetch works (if ngrok is accessible)
- [ ] Clearing URL switches to local network
- [ ] API calls use correct URL (check console)
- [ ] Login works with ngrok URL
- [ ] Login works with local network IP

## Code Flow

1. **App Start:**
   ```
   main() → ApiService.init() → Loads ngrok_url from SharedPreferences → _cachedNgrokUrl set
   ```

2. **API Call:**
   ```
   API method → baseUrl getter → Checks _cachedNgrokUrl → Returns URL
   ```

3. **Update URL:**
   ```
   Server Settings → setNgrokUrl() → Saves to SharedPreferences → Updates _cachedNgrokUrl → Immediate effect
   ```

This implementation should be fully functional! 🎉

