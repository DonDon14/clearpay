# Development/Staging Environment Setup

This guide explains how to set up and manage separate development and production environments on Render.com.

## 🎯 Overview

- **Production (Main)**: `clearpay-web.onrender.com` - Deploys from `main` branch
- **Development/Staging**: `clearpay-web-dev.onrender.com` - Deploys from `development` branch

## 📋 Setup Steps

### Step 1: Create Development Branch

```bash
# Make sure all changes are committed
git add .
git commit -m "Prepare for development branch setup"

# Create and switch to development branch
git checkout -b development

# Push development branch to remote
git push -u origin development
```

### Step 2: Create Development Service on Render

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Click "New +"** → **"Blueprint"**
3. **Connect your repository** (if not already connected)
4. **Select `render.dev.yaml`** as the blueprint file
5. **Click "Apply"**

This will create:
- `clearpay-web-dev` service (deploys from `development` branch)
- `clearpay-db-dev` database (separate from production)

### Step 3: Verify Development Service

1. Wait for the deployment to complete
2. Visit: `https://clearpay-web-dev.onrender.com`
3. Verify it's working (should show login page)
4. Test with development data

## 🔄 Workflow

### Daily Development Workflow

```bash
# 1. Switch to development branch
git checkout development

# 2. Make your changes
# ... edit files ...

# 3. Commit changes
git add .
git commit -m "Your commit message"

# 4. Push to development branch (auto-deploys to dev site)
git push origin development

# 5. Test on development site
# Visit: https://clearpay-web-dev.onrender.com
```

### Deploying to Production

```bash
# 1. Make sure development branch is up to date and tested
git checkout development
git pull origin development

# 2. Switch to main branch
git checkout main

# 3. Merge development into main
git merge development

# 4. Push to main (auto-deploys to production)
git push origin main

# 5. Verify production site
# Visit: https://clearpay-web.onrender.com
```

## 🔐 Environment Differences

### Production (`main` branch)
- **Environment**: `CI_ENVIRONMENT = production`
- **Service Name**: `clearpay-web`
- **Database**: `clearpay-db` (production data)
- **URL**: `https://clearpay-web.onrender.com`
- **Auto-deploy**: Yes (on push to `main`)

### Development (`development` branch)
- **Environment**: `CI_ENVIRONMENT = development`
- **Service Name**: `clearpay-web-dev`
- **Database**: `clearpay-db-dev` (development/test data)
- **URL**: `https://clearpay-web-dev.onrender.com`
- **Auto-deploy**: Yes (on push to `development`)

## 📝 Important Notes

### 1. Separate Databases
- Production and development use **completely separate databases**
- Development database is safe for testing and can be reset
- Production database contains real user data - **be careful!**

### 2. Code Changes
- Always develop on `development` branch first
- Test thoroughly on development site before merging to `main`
- Only merge to `main` when code is ready for production

### 3. Environment Variables
- Both environments auto-generate encryption keys
- You can set custom encryption keys in Render dashboard if needed
- Development environment shows more detailed error messages

### 4. Free Tier Limitations
- Both services can run on free tier
- Free tier services spin down after 15 minutes of inactivity
- First request after spin-down may take 30-60 seconds

## 🛠️ Troubleshooting

### Development site not updating?
```bash
# Check if you're on the right branch
git branch

# Make sure you pushed to development branch
git push origin development

# Check Render dashboard for deployment status
```

### Need to reset development database?
1. Go to Render Dashboard
2. Navigate to `clearpay-db-dev`
3. Delete and recreate the database
4. Redeploy the service (migrations will run automatically)

### Want to test production code locally?
```bash
# Switch to main branch
git checkout main

# Pull latest changes
git pull origin main

# Test locally
# Your local environment will use main branch code
```

## 📊 Branch Strategy

```
main (Production)
  ↑
  | (merge when ready)
  |
development (Development/Staging)
  ↑
  | (work here)
  |
feature branches (optional)
```

## ✅ Quick Reference

| Action | Command |
|--------|---------|
| Switch to development | `git checkout development` |
| Switch to production | `git checkout main` |
| Push to dev site | `git push origin development` |
| Push to prod site | `git push origin main` |
| Merge dev → prod | `git checkout main && git merge development && git push origin main` |
| Dev URL | https://clearpay-web-dev.onrender.com |
| Prod URL | https://clearpay-web.onrender.com |

## 🎉 You're All Set!

- **Development site**: Test new features safely
- **Production site**: Stable, production-ready code
- **Separate databases**: No risk of affecting production data

Happy coding! 🚀

