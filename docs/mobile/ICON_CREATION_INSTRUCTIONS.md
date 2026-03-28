# Icon Creation Instructions

To create proper favicon and app icons from the ClearPay logo with padding, follow these steps:

## Option 1: Using Online Tools (Recommended)

1. Go to https://realfavicongenerator.net/ or https://www.favicon-generator.org/
2. Upload `public/uploads/logo.png`
3. Configure settings:
   - Add padding: 20% around the logo
   - Generate sizes: 32x32 (favicon), 192x192, 512x512
4. Download and place files:
   - `favicon.png` → `flutter_app/web/favicon.png`
   - `favicon.ico` → `public/favicon.ico` (if generated)
   - `Icon-192.png` → `flutter_app/web/icons/Icon-192.png`
   - `Icon-512.png` → `flutter_app/web/icons/Icon-512.png`
   - `Icon-maskable-192.png` → `flutter_app/web/icons/Icon-maskable-192.png` (with 30% padding)
   - `Icon-maskable-512.png` → `flutter_app/web/icons/Icon-maskable-512.png` (with 30% padding)

## Option 2: Using Python Script

If you have Python and Pillow installed:

```bash
pip install Pillow
python create_icons.py
```

The script will automatically:
- Create favicon.png for web app (32x32 with 20% padding)
- Create Flutter web favicon.png (32x32 with 20% padding)
- Create app icons (192x192 and 512x512 with 20% padding)
- Create maskable icons (192x192 and 512x512 with 30% padding for safe zone)

## Option 3: Using Image Editing Software

1. Open `public/uploads/logo.png` in your image editor
2. Create a new square canvas (e.g., 512x512 for app icons, 32x32 for favicon)
3. Add padding around the logo (20% of canvas size)
4. Center the logo in the canvas
5. Export as PNG
6. Repeat for each required size

## Required Sizes

- **Favicon**: 32x32 pixels (20% padding)
- **App Icon (Regular)**: 192x192 and 512x512 pixels (20% padding)
- **App Icon (Maskable)**: 192x192 and 512x512 pixels (30% padding for safe zone)

## Current Status

The web app layouts have been updated to use the logo directly. For best results, generate proper sized icons using one of the methods above.

