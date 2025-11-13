# Cloudinary Setup Guide

## 📋 Step 1: Get Your Cloudinary Credentials

1. **Log into your Cloudinary account**: https://cloudinary.com/console
2. **Go to Dashboard**: You'll see your account details
3. **Copy these three values:**
   - **Cloud Name** (e.g., `dxyz123abc`)
   - **API Key** (e.g., `123456789012345`)
   - **API Secret** (e.g., `abcdefghijklmnopqrstuvwxyz123456`)

   > **Note**: The API Secret is sensitive - keep it secure!

## 🔧 Step 2: Add Environment Variables to Render

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Select your service** (clearpay-web-dev or clearpay-web)
3. **Go to Environment tab**
4. **Add these three environment variables:**

   ```
   CLOUDINARY_CLOUD_NAME = your_cloud_name_here
   CLOUDINARY_API_KEY = your_api_key_here
   CLOUDINARY_API_SECRET = your_api_secret_here
   ```

5. **Click "Save Changes"**
6. **Redeploy your service** (or wait for auto-deploy if enabled)

## ✅ Step 3: Verify Setup

After deployment, check the logs for:
- `Cloudinary service initialized successfully` ✅
- If you see `Cloudinary credentials not configured`, check your environment variables

## 🧪 Step 4: Test Upload

1. Upload a profile picture through the app
2. Check logs for: `Profile picture uploaded to Cloudinary successfully`
3. The profile picture URL should start with `https://res.cloudinary.com/`

## 📝 How It Works

- **If Cloudinary is configured**: Files upload directly to Cloudinary and persist forever
- **If Cloudinary is NOT configured**: Falls back to local storage (files lost on redeploy on Render)

## 🔍 Troubleshooting

### Issue: "Cloudinary credentials not configured"
- **Solution**: Check that all three environment variables are set in Render dashboard

### Issue: "Cloudinary upload failed"
- **Solution**: 
  1. Verify your API credentials are correct
  2. Check Cloudinary dashboard for any account limits
  3. Check Render logs for detailed error messages

### Issue: Profile pictures still disappearing
- **Solution**: 
  1. Verify Cloudinary is being used (check logs for "Cloudinary upload")
  2. Check that environment variables are set correctly
  3. Ensure service was redeployed after adding environment variables

## 💡 Free Tier Limits

Cloudinary free tier includes:
- **25 GB** storage
- **25 GB** bandwidth/month
- Perfect for profile pictures and small uploads

## 🔐 Security Note

**Never commit your API Secret to git!** Always use environment variables.

