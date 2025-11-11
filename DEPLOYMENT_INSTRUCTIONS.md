# Clear Instructions: Deploy Backend & Rebuild Flutter App

## ✅ What Changed

1. **Backend (PHP)**: Updated `loginPost()` to return JSON for mobile requests
2. **Flutter App**: Changed endpoint from `/api/payer/login` to `/payer/loginPost`
3. **Flutter App**: Updated base URL to production (`https://clearpay.infinityfreeapp.com`)

---

## 📤 Step 1: Upload Backend Files to InfinityFree

Upload these **2 files** to your InfinityFree hosting:

### Files to Upload:
1. ✅ `app/Controllers/Payer/LoginController.php`
2. ✅ `app/Config/Routes.php`

### How to Upload:
1. Log in to InfinityFree File Manager (or use FTP)
2. Navigate to your site's root directory
3. Upload the files to the same paths:
   - `app/Controllers/Payer/LoginController.php`
   - `app/Config/Routes.php`
4. **Replace** the existing files

---

## 🔨 Step 2: Rebuild Flutter App (APK)

Since we changed the Flutter code, you **MUST rebuild** the APK.

### Option A: Build APK (Recommended for Testing)

```bash
cd flutter_app
flutter clean
flutter pub get
flutter build apk --release
```

**Output location:** `flutter_app/build/app/outputs/flutter-apk/app-release.apk`

### Option B: Build App Bundle (For Google Play Store)

```bash
cd flutter_app
flutter clean
flutter pub get
flutter build appbundle --release
```

**Output location:** `flutter_app/build/app/outputs/bundle/release/app-release.aab`

### Option C: Build and Install Directly (Fastest for Testing)

```bash
cd flutter_app
flutter clean
flutter pub get
flutter run --release
```

This will build and install on a connected device/emulator.

---

## 📱 Step 3: Install & Test

### If you built APK:
1. Transfer `app-release.apk` to your Android device
2. Enable "Install from Unknown Sources" in Android settings
3. Install the APK
4. Open the app and test login

### If you used `flutter run`:
- The app will automatically install and launch

### Test Login:
- Use the same credentials that worked in the browser test:
  - Payer ID: `12345`
  - Password: `Thirdy`

---

## ✅ Verification Checklist

After rebuilding and installing:

- [ ] App opens without crashes
- [ ] Login screen loads
- [ ] Can enter credentials
- [ ] Login button works
- [ ] Receives JSON response (check logs)
- [ ] Successfully logs in and navigates to dashboard

---

## 🐛 If Login Still Fails

### Check Flutter Logs:
```bash
flutter logs
```

Look for:
- The URL being called (should be `https://clearpay.infinityfreeapp.com/payer/loginPost`)
- Response status code
- Response body (should be JSON, not HTML)

### Common Issues:

1. **Still getting HTML response:**
   - Verify backend files were uploaded correctly
   - Check InfinityFree file permissions
   - Clear browser cache and test endpoint again

2. **Connection timeout:**
   - Check device internet connection
   - Verify base URL is correct in `api_service.dart`

3. **Build errors:**
   - Run `flutter clean` and `flutter pub get` again
   - Check for any syntax errors in `api_service.dart`

---

## 📝 Quick Command Reference

```bash
# Navigate to Flutter app
cd flutter_app

# Clean previous builds
flutter clean

# Get dependencies
flutter pub get

# Build APK
flutter build apk --release

# OR build and run directly
flutter run --release
```

---

## 🎯 Summary

**YES, you need to rebuild the APK** because:
- ✅ We changed the API endpoint URL
- ✅ We changed the base URL to production
- ✅ The old APK still has the old code

**After rebuilding:**
- ✅ New APK will use the correct endpoint
- ✅ Will connect to production server
- ✅ Should work like the browser test did

---

## ⚡ Fast Track (If You Just Want to Test Quickly)

```bash
cd flutter_app
flutter clean && flutter pub get && flutter run --release
```

This will:
1. Clean old build
2. Get dependencies
3. Build and install on connected device
4. Launch the app

Then test login immediately!

