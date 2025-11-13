# ⚡ Quick Fix: Use Brevo API on Render

## 🚨 Problem Confirmed

**Port 465 with SSL also failed** - Render is definitely blocking SMTP ports.

**Solution**: Use **Brevo API** (HTTPS) instead of SMTP.

---

## ✅ Quick Steps

### Step 1: Get Brevo API Key

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Settings** → **SMTP & API** → **API Keys** tab
   - ⚠️ **NOT the SMTP tab** - you need the **API Keys** tab!
3. **Click**: **"Generate a new API key"**
4. **Name it**: "ClearPay Render"
5. **Copy the key** - it starts with `xkeysib-...`
6. **⚠️ You can only see it once!**

### Step 2: Add to Render

1. **Go to Render Dashboard** → Your service → **Environment**
2. **Add new variable**:
   - **Key**: `BREVO_API_KEY`
   - **Value**: `xkeysib-...` (your API key)
3. **Save**

### Step 3: Redeploy & Test

1. **Redeploy** (or wait for auto-deploy)
2. **Test email** from admin panel
3. **Should work!** ✅

---

## 🔍 What Changed

I've updated the code to:
- ✅ **Automatically detect** Brevo configuration
- ✅ **Try Brevo API first** if `BREVO_API_KEY` is set
- ✅ **Fall back to SMTP** if API key not found (for localhost)

**This means:**
- **Render**: Uses API (works!) ✅
- **Localhost**: Uses SMTP (works!) ✅

---

## 📝 Difference Between Keys

- **SMTP Key**: `xsmtpsib-...` (90 chars) - For SMTP only, **blocked on Render**
- **API Key**: `xkeysib-...` (different format) - For API, **works on Render** ✅

**You need BOTH:**
- SMTP key for localhost testing
- API key for Render deployment

---

## 🎯 After Setup

Once `BREVO_API_KEY` is set in Render:
- Emails will automatically use Brevo API
- No more port blocking issues
- Works on Render free tier! ✅

