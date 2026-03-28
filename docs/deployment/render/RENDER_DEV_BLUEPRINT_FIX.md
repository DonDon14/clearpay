# Fix: Render Blueprint Only Uses render.yaml

## Problem
Render Blueprints automatically look for `render.yaml` in the repository root. You cannot select a different file like `render.dev.yaml`.

## Solution: Temporarily Rename Files

### Quick Method (Recommended)

1. **On your development branch**, temporarily swap the files:

```bash
# Make sure you're on development branch
git checkout development

# Backup production render.yaml (if it exists)
mv render.yaml render.prod.yaml

# Copy dev config to render.yaml
cp render.dev.yaml render.yaml

# Commit the change
git add render.yaml render.prod.yaml
git commit -m "Temporarily use render.dev.yaml for Blueprint creation"
git push origin development
```

2. **Create Blueprint on Render**:
   - Go to Render Dashboard
   - Click "New +" → "Blueprint"
   - Select your repository
   - Select `development` branch
   - Render will now use `render.yaml` (which contains dev config)
   - Click "Deploy Blueprint"

3. **After Blueprint is created, restore files**:

```bash
# Restore original structure
git checkout development
mv render.prod.yaml render.yaml
git add render.yaml
git commit -m "Restore render.yaml structure"
git push origin development
```

### Alternative: Manual Service Creation

If you prefer not to rename files, you can create the services manually:

1. **Create Database**:
   - "New +" → "PostgreSQL"
   - Name: `clearpay-db-dev`
   - Plan: Free
   - Region: Oregon

2. **Create Web Service**:
   - "New +" → "Web Service"
   - Connect repository
   - Branch: `development`
   - Environment: Docker
   - Dockerfile Path: `./Dockerfile`
   - Environment Variables:
     - `CI_ENVIRONMENT` = `development`
     - `APP_TIMEZONE` = `Asia/Manila`
     - `DATABASE_URL` = (from database you created)
   - Health Check: `/health.php`

See `RENDER_DEV_MANUAL_SETUP.md` for detailed manual setup instructions.

## Why This Happens

Render Blueprints are designed to use a single `render.yaml` file per repository. This is by design to keep the configuration simple. The workaround is to temporarily rename files or create services manually.

## Recommendation

**Use the temporary rename method** - it's quick and lets you use the Blueprint feature, which is easier than manual setup.

