# 🔧 Render SMTP Port Blocking - Solution

## ✅ Good News: Configuration is Correct!

**Diagnostics show:**
- ✅ Config source: database
- ✅ SMTP Host: `smtp-relay.brevo.com`
- ✅ SMTP User: `9b7dd5001@smtp-brevo.com`
- ✅ SMTP Password: SET (90 chars)
- ✅ SMTP Port: 587
- ✅ SMTP Crypto: tls

**But email still fails on Render!**

---

## 🚨 Issue: Render Free Tier Blocks SMTP Ports

**Render's free tier often blocks outbound SMTP connections** on ports 25, 587, and 465.

**This is why:**
- ✅ Works on localhost (no port blocking)
- ❌ Fails on Render (SMTP ports blocked)

---

## ✅ Solution: Use Brevo API Instead of SMTP

**Brevo provides an API** that works over HTTPS (port 443), which Render doesn't block!

### Option 1: Use Brevo API (Recommended)

**Brevo API uses HTTPS (port 443)**, which Render doesn't block.

**Steps:**
1. **Get Brevo API Key**:
   - Login to Brevo: https://app.brevo.com
   - Go to: **Settings** → **SMTP & API** → **API Keys** tab
   - Click **"Generate a new API key"**
   - Copy the API key (starts with `xkeysib-...`)

2. **Install Brevo PHP SDK**:
   ```bash
   composer require getbrevo/brevo-php
   ```

3. **Update Email Controller** to use Brevo API instead of SMTP

### Option 2: Try Port 465 with SSL

**Sometimes port 465 (SSL) works when 587 (TLS) doesn't:**

1. **Update database settings** via admin panel:
   - SMTP Port: `465`
   - Encryption: `SSL` (not TLS)
   - Save and test

2. **Or update Render environment variables**:
   - `email.SMTPPort` = `465`
   - `email.SMTPCrypto` = `ssl`

### Option 3: Upgrade Render Plan

**Paid Render plans** don't block SMTP ports:
- Upgrade to **Starter** plan ($7/month)
- SMTP ports will work

---

## 🔍 Quick Test: Try Port 465

**Before switching to API, try port 465:**

1. **Go to admin panel** → Settings → SMTP Configuration
2. **Change**:
   - Port: `465`
   - Encryption: `SSL`
3. **Save Configuration**
4. **Send Test Email**

**If this works**, Render allows port 465 but blocks 587.

---

## 📊 Why This Happens

**Render Free Tier Limitations:**
- Blocks outbound connections on ports 25, 587 (SMTP)
- Allows HTTPS (port 443) - this is why API works
- Allows some other ports, but SMTP is restricted

**This is a common issue** with free hosting tiers (Heroku, Render, etc.)

---

## ✅ Recommended: Switch to Brevo API

**Benefits:**
- ✅ Works on Render free tier (uses HTTPS)
- ✅ More reliable than SMTP
- ✅ Better error messages
- ✅ Faster delivery

**I can help you implement the Brevo API integration if you want!**

---

## 🚀 Next Steps

1. **Try port 465 with SSL first** (quick test)
2. **If that fails**, switch to Brevo API
3. **Or upgrade Render plan** (if you prefer SMTP)

**Which option would you like to try first?**

