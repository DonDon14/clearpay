# Update App Icon - Instructions

The app icons have been generated from the ClearPay logo with 20% padding. To see the new icon on your emulator/device, follow these steps:

## For Android Emulator/Device:

1. **Clean the build:**
   ```bash
   cd flutter_app
   flutter clean
   ```

2. **Rebuild the app:**
   ```bash
   flutter build apk
   # OR for debug build:
   flutter run
   ```

3. **Uninstall the old app** from your emulator/device (if it's already installed)

4. **Reinstall the app:**
   ```bash
   flutter install
   # OR run it directly:
   flutter run
   ```

## For iOS Simulator/Device:

1. **Clean the build:**
   ```bash
   cd flutter_app
   flutter clean
   ```

2. **Rebuild the app:**
   ```bash
   flutter build ios
   # OR for debug build:
   flutter run
   ```

3. **Uninstall the old app** from your simulator/device

4. **Reinstall the app:**
   ```bash
   flutter run
   ```

## Quick Command (All Platforms):

```bash
cd flutter_app
flutter clean
flutter run
```

**Note:** If the icon still doesn't update after rebuilding:
- Make sure you completely uninstalled the old app
- Clear the app data/cache
- Restart the emulator/device
- Try a full rebuild: `flutter clean && flutter pub get && flutter run`

## What Was Created:

✅ **Android Icons:**
- mipmap-mdpi/ic_launcher.png (48x48)
- mipmap-hdpi/ic_launcher.png (72x72)
- mipmap-xhdpi/ic_launcher.png (96x96)
- mipmap-xxhdpi/ic_launcher.png (144x144)
- mipmap-xxxhdpi/ic_launcher.png (192x192)

✅ **iOS Icons:**
- All required sizes from 20x20 to 1024x1024
- iPhone and iPad variants
- Marketing icon (1024x1024)

✅ **Web Icons:**
- Favicon (32x32)
- PWA icons (192x192, 512x512)
- Maskable icons for adaptive icons

All icons use the ClearPay logo with 20% padding around the edges.

