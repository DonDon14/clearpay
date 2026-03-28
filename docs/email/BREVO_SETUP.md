# ✅ Brevo Email Setup (Recommended for Gmail)

**Brevo** (formerly Sendinblue) is the **best option** if you want to send from a Gmail address (`project.clearpay@gmail.com`) because it **doesn't require domain verification**.

---

## 🚀 Quick Setup Steps

### Step 1: Sign Up for Brevo

1. **Go to**: https://www.brevo.com
2. **Click "Sign up free"**
3. **Create account** (very easy approval, usually instant)
4. **Free tier**: 300 emails/day (plenty for development)

### Step 2: Verify Sender Email Address ⚠️ **REQUIRED FIRST**

**Before getting SMTP credentials, you MUST verify the sender email address!**

1. **Go to**: **Settings** → **Senders** (in left sidebar)
2. **Click**: **"Add a sender"** or **"Verify a sender"**
3. **Enter**: `project.clearpay@gmail.com`
4. **Click**: **"Send verification email"**
5. **You'll see a warning dialog** about free email addresses:
   - ⚠️ **Click**: **"Add this sender anyway"** (left button, blue text)
   - This warning is about deliverability (emails may go to spam), but it will work!
   - You can authenticate your own domain later for better deliverability
6. **Check your Gmail inbox** for `project.clearpay@gmail.com`
7. **Open the email from Brevo** and **click the verification link**
8. **Wait for verification** (usually instant, shows as "Verified" ✅)

**⚠️ IMPORTANT**: You **cannot send emails** until the sender is verified!

---

### Step 3: Get SMTP Credentials

**After verifying the sender, get your SMTP credentials:**

1. **Go to**: **Settings** → **SMTP & API** → **SMTP** tab
2. **You should see**:
   - **SMTP Server**: `smtp-relay.brevo.com` ✅
   - **Port**: `587` ✅
   - **Login**: `9b7dd5001@smtp-brevo.com` (or similar - this is your SMTP username)

2. **Generate SMTP Password**:
   - **Click the black button**: **"Generate a new SMTP key"** (top right)
   - **Give it a name** (e.g., "ClearPay Production" or "ClearPay Render")
   - **Copy the SMTP key** that appears - **THIS IS YOUR SMTP PASSWORD** ⚠️
   - ⚠️ **IMPORTANT**: You can only see this password once! Copy it immediately.

3. **Your SMTP Credentials**:
   - **SMTP Host**: `smtp-relay.brevo.com`
   - **SMTP User**: `9b7dd5001@smtp-brevo.com` (the Login shown on the page)
   - **SMTP Password**: `[The SMTP key you just generated]` (the password/key you copied)
   - **Port**: `587`
   - **Encryption**: `TLS`

### Step 4: Configure in ClearPay (After Verification)

#### Option A: Via Render Dashboard

1. **Go to Render Dashboard** → **Your service** → **Environment**
2. **Update these variables**:
   ```
   email.SMTPHost = smtp-relay.brevo.com
   email.SMTPUser = 9b7dd5001@smtp-brevo.com
   email.SMTPPass = [Your generated SMTP key - the password you copied]
   email.SMTPPort = 587
   email.SMTPCrypto = tls
   email.fromEmail = project.clearpay@gmail.com
   email.fromName = ClearPay
   ```
   **Example**:
   ```
   email.SMTPHost = smtp-relay.brevo.com
   email.SMTPUser = 9b7dd5001@smtp-brevo.com
   email.SMTPPass = xkeysib-abc123def456...
   email.SMTPPort = 587
   email.SMTPCrypto = tls
   email.fromEmail = project.clearpay@gmail.com
   email.fromName = ClearPay
   ```
3. **Save** and wait for redeploy

#### Option B: Via Admin Panel

1. **Login to your Render deployment**
2. **Go to Settings** → **SMTP Configuration**
3. **Enter**:
   - **SMTP Host**: `smtp-relay.brevo.com`
   - **SMTP User**: `9b7dd5001@smtp-brevo.com` (the Login from Brevo page)
   - **SMTP Password**: `[Your generated SMTP key]` (the password/key you copied)
   - **Port**: `587`
   - **Encryption**: `TLS`
   - **From Email**: `project.clearpay@gmail.com`
   - **From Name**: `ClearPay`
4. **Save Configuration**
5. **Send Test Email** ✅

---

## ✅ Brevo SMTP Configuration Summary

| Setting | Value |
|---------|-------|
| **SMTP Host** | `smtp-relay.brevo.com` |
| **SMTP User** | `9b7dd5001@smtp-brevo.com` (your Login from Brevo) |
| **SMTP Password** | `[Your generated SMTP key]` (the key you copied) |
| **Port** | `587` |
| **Encryption** | `TLS` |
| **From Email** | `project.clearpay@gmail.com` ✅ |
| **From Name** | `ClearPay` |

---

## 🎯 Why Brevo is Better for Gmail

- ✅ **No domain verification required** - Can send from Gmail addresses
- ✅ **Easy approval** - Usually instant
- ✅ **Free tier**: 300 emails/day
- ✅ **Works perfectly on Render**
- ✅ **No port blocking issues**
- ✅ **Good deliverability**

---

## 📧 Brevo Free Tier

- **300 emails/day** (9,000/month)
- **No credit card required**
- **Perfect for development and small projects**

---

## 🧪 Testing

1. **Configure SMTP settings** (see above)
2. **Go to Settings** → **SMTP Configuration**
3. **Click "Send Test"**
4. **Check your inbox** - email should arrive!

### ⚠️ Email Not in Inbox?

**If Brevo shows the email was sent but you don't see it:**

1. **Check Spam/Junk folder** ⚠️ **MOST COMMON**
   - Gmail: Check "Spam" folder
   - Other providers: Check "Junk" folder
   - Mark as "Not spam" if found

2. **Check Brevo Dashboard**
   - Go to: **Transactional** → **Emails**
   - Check delivery status (Delivered, Bounced, Pending)

3. **Check Promotions Tab** (Gmail)
   - Emails might go to "Promotions" instead of "Primary"

4. **Add Sender to Contacts**
   - If found in spam, add `project.clearpay@gmail.com` to contacts

**👉 See `BREVO_EMAIL_DELIVERY_TROUBLESHOOTING.md` for detailed troubleshooting**

---

## 🔒 Security

- **SMTP Password** is sensitive - don't share it
- **Store in Render environment variables** (not in code)
- **Or save in database** via admin panel (encrypted)

---

## 🆚 Brevo vs Resend

| Feature | Brevo | Resend |
|---------|-------|--------|
| **Gmail addresses** | ✅ Yes | ❌ No (domain required) |
| **Free tier** | 300/day | 3,000/month |
| **Approval** | ⭐⭐⭐⭐⭐ Easy | ⭐⭐⭐⭐ Easy |
| **Domain verification** | ❌ Not required | ✅ Required |

**For Gmail addresses, Brevo is the better choice!** 🎯

