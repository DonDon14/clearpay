# Manual Development Service Setup on Render

Since Render Blueprints only look for `render.yaml` by default, here's how to set up the development environment manually.

## Option 1: Create Services Manually (Recommended)

### Step 1: Create Development Database

1. Go to Render Dashboard: https://dashboard.render.com
2. Click **"New +"** → **"PostgreSQL"**
3. Configure:
   - **Name**: `clearpay-db-dev`
   - **Database**: `clearpaydb_dev`
   - **User**: `clearpay_user_dev`
   - **Plan**: `Free`
   - **Region**: `Oregon` (or same as production)
4. Click **"Create Database"**
5. **Note the connection details** (you'll need them)

### Step 2: Create Development Web Service

1. Go to Render Dashboard
2. Click **"New +"** → **"Web Service"**
3. Connect your repository (if not already connected)
4. Configure:
   - **Name**: `clearpay-web-dev`
   - **Environment**: `Docker`
   - **Region**: `Oregon` (or same as production)
   - **Branch**: `development`
   - **Root Directory**: Leave empty (or `/`)
   - **Dockerfile Path**: `./Dockerfile`
   - **Docker Context**: `.`
   - **Plan**: `Free`
   - **Auto-Deploy**: `Yes`

5. **Environment Variables**:
   - `CI_ENVIRONMENT` = `development`
   - `APP_TIMEZONE` = `Asia/Manila`
   - `DATABASE_URL` = (from the database you just created - use "Internal Database URL")

6. **Health Check Path**: `/health.php`

7. Click **"Create Web Service"**

### Step 3: Link Database to Web Service

1. Go to your `clearpay-web-dev` service
2. Go to **"Environment"** tab
3. Find `DATABASE_URL` variable
4. Click **"Link Database"** or manually set it to the internal database URL from `clearpay-db-dev`

## Option 2: Temporarily Rename Files (Alternative)

If you prefer to use the Blueprint approach:

1. **Temporarily rename files**:
   ```bash
   git checkout development
   mv render.yaml render.prod.yaml
   mv render.dev.yaml render.yaml
   git add .
   git commit -m "Use render.dev.yaml for development branch"
   git push origin development
   ```

2. **Create Blueprint** on Render (it will use `render.yaml`)

3. **Rename back**:
   ```bash
   mv render.yaml render.dev.yaml
   mv render.prod.yaml render.yaml
   git add .
   git commit -m "Restore render.yaml structure"
   git push origin development
   ```

## Option 3: Use Same Blueprint File with Branch Detection

We can modify `render.yaml` to detect the branch and configure accordingly. However, this is more complex.

## Recommended: Option 1 (Manual Setup)

Manual setup gives you:
- ✅ Full control over service names
- ✅ Clear separation between environments
- ✅ No need to rename files
- ✅ Easy to understand and maintain

## After Setup

Your services will be:
- **Production**: `clearpay-web` (from `main` branch)
- **Development**: `clearpay-web-dev` (from `development` branch)

Both will auto-deploy when you push to their respective branches!

