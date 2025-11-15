"""
Script to create favicon and app icons from logo.png with padding
Requires: Pillow (pip install Pillow)
"""
from PIL import Image, ImageDraw
import os

def create_icon_with_padding(input_path, output_path, size, padding_percent=20):
    """
    Create an icon from a logo with padding around it
    
    Args:
        input_path: Path to the source logo image
        output_path: Path to save the output icon
        size: Output size (e.g., 32, 192, 512)
        padding_percent: Percentage of padding around the logo (default 20%)
    """
    # Open the source logo
    logo = Image.open(input_path)
    
    # Convert to RGBA if needed
    if logo.mode != 'RGBA':
        logo = logo.convert('RGBA')
    
    # Calculate padding in pixels
    padding = int(size * padding_percent / 100)
    content_size = size - (padding * 2)
    
    # Resize logo to fit within the content area (maintaining aspect ratio)
    logo.thumbnail((content_size, content_size), Image.Resampling.LANCZOS)
    
    # Create a new image with transparent background
    icon = Image.new('RGBA', (size, size), (0, 0, 0, 0))
    
    # Calculate position to center the logo
    x_offset = (size - logo.width) // 2
    y_offset = (size - logo.height) // 2
    
    # Paste the logo onto the icon
    icon.paste(logo, (x_offset, y_offset), logo)
    
    # Save the icon
    icon.save(output_path, 'PNG')
    print(f"Created {output_path} ({size}x{size})")

def main():
    # Paths
    logo_path = 'public/uploads/logo.png'
    
    if not os.path.exists(logo_path):
        print(f"Error: Logo not found at {logo_path}")
        return
    
    # Use minimal padding (5%) for app icons to fill the space like other apps
    app_icon_padding = 5
    
    # Create favicon for web app (public folder)
    print("Creating favicon for web app...")
    create_icon_with_padding(logo_path, 'public/favicon.png', 32, padding_percent=app_icon_padding)
    
    # Also create favicon.ico (convert PNG to ICO)
    try:
        favicon_png = Image.open('public/favicon.png')
        # ICO format requires multiple sizes, but we'll create a simple one
        favicon_png.save('public/favicon.ico', format='ICO', sizes=[(32, 32)])
        print("Created public/favicon.ico (32x32)")
    except Exception as e:
        print(f"Note: Could not create .ico file: {e}")
        print("PNG favicon will work fine for modern browsers")
    
    # Create Flutter web favicon
    print("Creating Flutter web favicon...")
    create_icon_with_padding(logo_path, 'flutter_app/web/favicon.png', 32, padding_percent=app_icon_padding)
    
    # Create Flutter web app icons
    print("Creating Flutter web app icons...")
    icon_sizes = [192, 512]
    for size in icon_sizes:
        # Regular icons
        create_icon_with_padding(
            logo_path, 
            f'flutter_app/web/icons/Icon-{size}.png', 
            size, 
            padding_percent=app_icon_padding
        )
        # Maskable icons (with more padding for safe zone)
        create_icon_with_padding(
            logo_path, 
            f'flutter_app/web/icons/Icon-maskable-{size}.png', 
            size, 
            padding_percent=20
        )
    
    # Create Android app icons
    print("\nCreating Android app icons...")
    android_sizes = {
        'mipmap-mdpi': 48,
        'mipmap-hdpi': 72,
        'mipmap-xhdpi': 96,
        'mipmap-xxhdpi': 144,
        'mipmap-xxxhdpi': 192
    }
    for folder, size in android_sizes.items():
        create_icon_with_padding(
            logo_path,
            f'flutter_app/android/app/src/main/res/{folder}/ic_launcher.png',
            size,
            padding_percent=app_icon_padding
        )
    
    # Create iOS app icons
    print("\nCreating iOS app icons...")
    ios_icons = [
        # iPhone
        ('Icon-App-20x20@2x.png', 40),   # 20x20 @2x = 40x40
        ('Icon-App-20x20@3x.png', 60),   # 20x20 @3x = 60x60
        ('Icon-App-29x29@1x.png', 29),   # 29x29 @1x = 29x29
        ('Icon-App-29x29@2x.png', 58),   # 29x29 @2x = 58x58
        ('Icon-App-29x29@3x.png', 87),   # 29x29 @3x = 87x87
        ('Icon-App-40x40@2x.png', 80),   # 40x40 @2x = 80x80
        ('Icon-App-40x40@3x.png', 120),  # 40x40 @3x = 120x120
        ('Icon-App-60x60@2x.png', 120),  # 60x60 @2x = 120x120
        ('Icon-App-60x60@3x.png', 180),  # 60x60 @3x = 180x180
        # iPad
        ('Icon-App-20x20@1x.png', 20),   # 20x20 @1x = 20x20
        ('Icon-App-40x40@1x.png', 40),   # 40x40 @1x = 40x40
        ('Icon-App-76x76@1x.png', 76),   # 76x76 @1x = 76x76
        ('Icon-App-76x76@2x.png', 152),  # 76x76 @2x = 152x152
        ('Icon-App-83.5x83.5@2x.png', 167), # 83.5x83.5 @2x = 167x167
        # Marketing
        ('Icon-App-1024x1024@1x.png', 1024), # 1024x1024 @1x = 1024x1024
    ]
    
    ios_path = 'flutter_app/ios/Runner/Assets.xcassets/AppIcon.appiconset'
    for filename, size in ios_icons:
        create_icon_with_padding(
            logo_path,
            f'{ios_path}/{filename}',
            size,
            padding_percent=app_icon_padding
        )
    
    print("\nAll icons created successfully!")
    print("\nNote: After generating icons, you may need to:")
    print("1. Rebuild the Flutter app: flutter clean && flutter build")
    print("2. Uninstall and reinstall the app on your device/emulator")
    print("3. Clear app data/cache if the icon doesn't update")

if __name__ == '__main__':
    main()

