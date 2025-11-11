# Testing the Login Endpoint

## Quick Test (Windows CMD)

**Option 1: Use the batch file**
```
Double-click: test-login-simple.bat
```

**Option 2: Use PowerShell (Better for JSON)**
```
Right-click test-login-endpoint.ps1 → Run with PowerShell
```

**Option 3: Single line command (Copy-paste)**
```cmd
curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost" -H "Content-Type: application/json" -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" -d "{\"payer_id\":\"12345\",\"password\":\"Thirdy\"}"
```

## Expected Results

### ✅ SUCCESS - JSON Response
If you see something like:
```json
{
  "success": true,
  "message": "Login successful",
  "data": { ... }
}
```
**→ The endpoint works!** Upload the updated files and test the Flutter app.

### ❌ FAILURE - HTML Response
If you see HTML like:
```html
<html><body><script type="text/javascript" src="/aes.js">...
```
**→ InfinityFree security is blocking it.** Try the form-data test.

### ❌ FAILURE - Connection Error
If you see:
```
curl: (56) schannel: server closed abruptly
```
**→ Server is closing the connection.** This might be:
- InfinityFree security blocking
- Network/firewall issue
- SSL/TLS problem

## Alternative: Test with Form Data

If JSON doesn't work, try form-data (less likely to be blocked):

```cmd
curl.exe -X POST "https://clearpay.infinityfreeapp.com/payer/loginPost?format=json" -H "Accept: application/json" -H "X-Requested-With: XMLHttpRequest" -d "payer_id=12345&password=Thirdy&format=json"
```

Or run: `test-login-form-data.bat`

## If Form Data Works

We can update the Flutter app to send form-data instead of JSON. This is a simple change in the API service.

## Next Steps

1. **Test the endpoint** using one of the scripts above
2. **Share the result** - What did you see?
3. **Based on result:**
   - ✅ JSON response → Upload files, test Flutter app
   - ❌ HTML/Error → We'll try form-data or contact InfinityFree support

## Files to Upload (If JSON Works)

1. `app/Controllers/Payer/LoginController.php` - Updated to handle mobile requests
2. `app/Config/Routes.php` - Added OPTIONS route
3. `flutter_app/lib/services/api_service.dart` - Updated endpoint URL

## Testing from Browser

You can also test from browser console:

```javascript
fetch('https://clearpay.infinityfreeapp.com/payer/loginPost', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  body: JSON.stringify({
    payer_id: '12345',
    password: 'Thirdy'
  })
})
.then(r => r.text())
.then(console.log)
.catch(console.error);
```

If this returns JSON in browser but curl fails, it might be a curl/SSL issue, not an endpoint issue.

