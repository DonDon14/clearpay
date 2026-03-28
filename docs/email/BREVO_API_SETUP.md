# 🚀 Brevo API Setup (Solution for Render Port Blocking)

## ⚠️ Problem: Render Blocks SMTP Ports

**Render's free tier blocks SMTP ports** (587, 465), so SMTP won't work even with correct configuration.

**Solution**: Use **Brevo API** instead (uses HTTPS/port 443, which Render doesn't block)

---

## ✅ Step 1: Get Brevo API Key

**The API key is DIFFERENT from the SMTP key!**

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Settings** → **SMTP & API** → **API Keys** tab (NOT SMTP tab)
3. **Click**: **"Generate a new API key"**
4. **Give it a name**: "ClearPay Render API"
5. **Copy the API key** - it starts with `xkeysib-...` (NOT `xsmtpsib-...`)
6. **⚠️ IMPORTANT**: You can only see this once! Copy it immediately.

**Note**: 
- **SMTP Key** (for SMTP): `xsmtpsib-...` (90 chars) - **Won't work on Render**
- **API Key** (for API): `xkeysib-...` (different format) - **Works on Render** ✅

---

## ✅ Step 2: Set API Key in Render

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Select your service**: `clearpay-web-dev-k3h3`
3. **Go to**: **Environment** tab
4. **Add new variable**:
   - **Key**: `BREVO_API_KEY`
   - **Value**: `xkeysib-...` (your Brevo API key)
5. **Save** and wait for redeploy

---

## ✅ Step 3: Test Email

1. **After Render redeploys** (2-5 minutes)
2. **Go to your app** → Settings → SMTP Configuration
3. **Click "Send Test"**
4. **Should work now!** ✅

The code will automatically:
- Try Brevo API first (works on Render)
- Fall back to SMTP if API fails (for localhost)

---

## 🔍 How It Works

**The code now:**
1. **Detects Brevo configuration** (SMTP host contains "brevo")
2. **Checks for API key** in environment variable `BREVO_API_KEY`
3. **Uses Brevo API** (HTTPS) if API key is found
4. **Falls back to SMTP** if API key not found (for localhost)

**This means:**
- ✅ **Render**: Uses Brevo API (bypasses port blocking)
- ✅ **Localhost**: Uses SMTP (works fine locally)

---

## 📝 Quick Checklist

- [ ] Get Brevo API key from Brevo dashboard (API Keys tab, not SMTP)
- [ ] Set `BREVO_API_KEY` in Render environment variables
- [ ] Wait for Render to redeploy
- [ ] Test email from admin panel
- [ ] Should work! ✅

---

## 🆘 If API Key Not Working

**Check:**
1. API key starts with `xkeysib-` (not `xsmtpsib-`)
2. API key is set in Render as `BREVO_API_KEY`
3. Sender email is verified in Brevo (Settings → Senders)
4. Check Render logs for API errors

---

## 💡 Why This Works

- **SMTP**: Uses ports 587/465 → **Blocked by Render** ❌
- **Brevo API**: Uses HTTPS (port 443) → **Works on Render** ✅

**Same Brevo service, different method!**

