# Profile Picture Storage Behavior

## How It Works

The system uses a **smart fallback approach** for profile picture storage:

1. **First Priority: Cloudinary** (if configured)
2. **Fallback: Local Storage** (if Cloudinary is not configured or upload fails)

## Current Behavior

### On Render (Production) - `clearpay-web-dev-k3h3.onrender.com`

✅ **Uses Cloudinary** because:
- Environment variables are set in Render dashboard:
  - `CLOUDINARY_CLOUD_NAME`
  - `CLOUDINARY_API_KEY`
  - `CLOUDINARY_API_SECRET`
- Profile pictures are uploaded to Cloudinary
- Database stores full Cloudinary URLs (e.g., `https://res.cloudinary.com/dgiycv3x6/image/upload/...`)
- Images persist across deployments (not lost on redeploy)

### On Localhost (Development) - `http://localhost/ClearPay/public`

📁 **Uses Local Storage** because:
- Cloudinary environment variables are **NOT set** in your `.env` file
- Profile pictures are saved to `public/uploads/profile/`
- Database stores relative paths (e.g., `uploads/profile/payer_2_1234567890.png`)
- Images are stored on your computer's filesystem

## How to Use Cloudinary on Localhost (Optional)

If you want to test Cloudinary uploads on localhost, add these to your `.env` file:

```env
# Cloudinary Configuration (Optional - for localhost testing)
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

**Note:** This is optional. Local storage works fine for development.

## How the System Decides

The code checks in this order:

1. **Check if Cloudinary is configured:**
   ```php
   $cloudinaryService = new \App\Services\CloudinaryService();
   if ($cloudinaryService->isConfigured()) {
       // Try Cloudinary upload
   }
   ```

2. **If Cloudinary is configured:**
   - Attempts upload to Cloudinary
   - If successful → Stores Cloudinary URL in database
   - If fails → Falls back to local storage

3. **If Cloudinary is NOT configured:**
   - Uses local storage directly
   - Stores relative path in database

## Database Storage Format

### Cloudinary URLs (Production)
```
https://res.cloudinary.com/dgiycv3x6/image/upload/v1763054345/profile/payer_2_1763054345.png
```

### Local Paths (Localhost)
```
uploads/profile/payer_2_1234567890.png
```

## Display Logic

The views automatically handle both formats:

```php
// Check if it's a full URL (Cloudinary) or local path
$profileUrl = (strpos($profilePicture, 'http://') === 0 || strpos($profilePicture, 'https://') === 0)
    ? $profilePicture  // Use Cloudinary URL as-is
    : base_url($profilePicture);  // Construct full URL for local path
```

## Summary

| Environment | Storage Used | Database Format | Persistence |
|------------|--------------|----------------|-------------|
| **Render (Production)** | Cloudinary | Full Cloudinary URL | ✅ Persistent (survives redeploy) |
| **Localhost (Development)** | Local Files | Relative path | ✅ Persistent (on your computer) |

## For Your Preliminary Edits on Localhost

✅ **Everything works normally:**
- Upload profile pictures → Saved to `public/uploads/profile/`
- View profile pictures → Loaded from local filesystem
- Edit and test → All changes work as expected
- No Cloudinary needed for local development

When you push to Render, the system automatically uses Cloudinary (because env vars are set there).

