# Render File Storage Issue - Profile Pictures Disappearing

## 🔴 Problem

Profile pictures uploaded to Render are **temporarily stored** and disappear after deployment. This happens because:

1. **Render's filesystem is ephemeral** - All files in the container are wiped when:
   - A new deployment occurs (git push)
   - The service restarts
   - The container is rebuilt

2. **What happens:**
   - User uploads profile picture → File saved to `public/uploads/profile/`
   - Database is updated with the file path ✅
   - File appears to work correctly ✅
   - **Next deployment occurs** → Container is rebuilt
   - **File is deleted** ❌
   - Database still has the path, but file doesn't exist ❌
   - Profile picture appears broken or reverts to old/default image

## ✅ Current Status

The code **correctly updates the database**, but the physical file is lost on redeploy because it's stored in the ephemeral filesystem.

## 🔧 Solutions

### Option 1: Cloud Storage (Recommended)

Use cloud storage services that persist files outside the container:

#### A. Cloudinary (Easiest - Free tier available)
- **Free tier**: 25GB storage, 25GB bandwidth/month
- **Setup**: 
  1. Sign up at https://cloudinary.com
  2. Get API key, secret, and cloud name
  3. Install: `composer require cloudinary/cloudinary_php`
  4. Update upload logic to use Cloudinary API

#### B. AWS S3 (Most scalable)
- **Free tier**: 5GB storage, 20,000 GET requests/month
- **Setup**:
  1. Create AWS account and S3 bucket
  2. Get access key and secret
  3. Install: `composer require aws/aws-sdk-php`
  4. Update upload logic to use S3 API

#### C. Google Cloud Storage
- **Free tier**: 5GB storage/month
- Similar setup to S3

### Option 2: Render Persistent Disk (Paid)

Render offers persistent disks that survive deployments:
- **Cost**: Starts at $0.25/GB/month
- **Setup**: Add disk in Render dashboard, mount to `/var/www/html/public/uploads`
- **Limitation**: Only available on paid plans

### Option 3: Database Storage (Not Recommended)

Store images as base64 in database:
- **Pros**: Files persist
- **Cons**: 
  - Database bloat
  - Performance issues
  - Not scalable
  - Max file size limits

## 🚀 Recommended Implementation: Cloudinary

### Step 1: Install Cloudinary

```bash
composer require cloudinary/cloudinary_php
```

### Step 2: Add Environment Variables

In Render dashboard, add:
- `CLOUDINARY_CLOUD_NAME` - Your cloud name
- `CLOUDINARY_API_KEY` - Your API key  
- `CLOUDINARY_API_SECRET` - Your API secret

### Step 3: Create Cloudinary Service

Create `app/Services/CloudinaryService.php`:

```php
<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

class CloudinaryService
{
    protected $cloudinary;
    
    public function __construct()
    {
        $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
        $apiKey = getenv('CLOUDINARY_API_KEY');
        $apiSecret = getenv('CLOUDINARY_API_SECRET');
        
        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            throw new \Exception('Cloudinary credentials not configured');
        }
        
        Configuration::instance([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret
            ],
            'url' => [
                'secure' => true
            ]
        ]);
        
        $this->cloudinary = new Cloudinary();
    }
    
    public function upload($filePath, $folder = 'profile')
    {
        $result = $this->cloudinary->uploadApi()->upload(
            $filePath,
            [
                'folder' => $folder,
                'resource_type' => 'image',
                'transformation' => [
                    ['width' => 400, 'height' => 400, 'crop' => 'fill', 'gravity' => 'face']
                ]
            ]
        );
        
        return $result['secure_url'];
    }
    
    public function delete($publicId)
    {
        return $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
```

### Step 4: Update Upload Controller

Modify `app/Controllers/Payer/DashboardController.php`:

```php
// After file validation, upload to Cloudinary instead of local filesystem
$cloudinaryService = new \App\Services\CloudinaryService();
$cloudinaryUrl = $cloudinaryService->upload($file->getTempName(), 'profile');

// Store Cloudinary URL in database instead of local path
$this->payerModel->update($payerId, ['profile_picture' => $cloudinaryUrl]);
```

## 📝 Current Workaround

Until cloud storage is implemented:

1. **For testing**: Profile pictures will work until next deployment
2. **For production**: Users will need to re-upload after each deployment
3. **Detection**: The system now logs warnings when files are missing

## 🔍 How to Detect the Issue

Check Render logs for:
- `Profile picture not found: /var/www/html/public/uploads/profile/...`
- `Profile picture uploaded on Render (ephemeral filesystem)`

## ⚠️ Important Notes

- **Database updates are working correctly** - The issue is file persistence
- **Local development works fine** - Files persist on your local machine
- **Only affects Render deployments** - Other hosting with persistent storage won't have this issue

