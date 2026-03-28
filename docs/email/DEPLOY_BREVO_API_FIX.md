# 🚀 Deploy Brevo API Fix to Render

## ⚠️ Current Issue

The code changes for Brevo API integration are **not yet deployed to Render**. The 500 error is likely because:

1. `BrevoEmailService` class doesn't exist on Render
2. Code changes haven't been committed/pushed to git
3. Render hasn't pulled the latest code

---

## ✅ Steps to Deploy

### Step 1: Commit Changes

```bash
git add .
git commit -m "Add Brevo API integration to bypass Render SMTP port blocking"
```

### Step 2: Push to Repository

```bash
git push origin development
```

### Step 3: Wait for Render Auto-Deploy

- Render will automatically detect the push
- It will rebuild and redeploy (takes 2-5 minutes)
- Check Render dashboard for deployment status

### Step 4: Set Brevo API Key (After Deploy)

1. **Get Brevo API Key**:
   - Go to: https://app.brevo.com
   - **Settings** → **SMTP & API** → **API Keys** tab
   - Generate new API key (starts with `xkeysib-...`)

2. **Add to Render**:
   - Render Dashboard → Your service → **Environment**
   - Add: `BREVO_API_KEY` = `xkeysib-...`
   - Save

3. **Redeploy** (or wait for auto-redeploy)

### Step 5: Test Email

- Go to admin panel → Settings
- Click "Send Test Email"
- Should work! ✅

---

## 🔍 What Changed

1. **Created `BrevoEmailService` class** - Sends emails via Brevo API (HTTPS)
2. **Updated `EmailSettingsController`** - Automatically uses API if `BREVO_API_KEY` is set
3. **Added fallback** - Uses SMTP on localhost, API on Render

---

## 📝 Files Changed

- `app/Services/BrevoEmailService.php` (NEW)
- `app/Controllers/Admin/EmailSettingsController.php` (UPDATED)
- `composer.json` (UPDATED - added getbrevo/brevo-php)

---

## ⚠️ Important Notes

- **Brevo API key is DIFFERENT from SMTP key**
  - SMTP key: `xsmtpsib-...` (won't work on Render)
  - API key: `xkeysib-...` (works on Render) ✅

- **Code must be deployed first** before API key will work
- **After deployment**, set `BREVO_API_KEY` in Render environment variables

---

## 🎯 Expected Behavior After Deploy

- **Without `BREVO_API_KEY`**: Falls back to SMTP (fails on Render due to port blocking)
- **With `BREVO_API_KEY`**: Uses Brevo API (works on Render) ✅

