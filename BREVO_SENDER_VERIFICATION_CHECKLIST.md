# ✅ Brevo Sender Verification Checklist

## ⚠️ Error: "Sender not valid"

**Error Message**: `"Sending has been rejected because the sender you used project.clearpay@gmail.com is not valid. Validate your sender or authenticate your domain."`

**This means**: The sender email is **NOT verified** in Brevo, or verification is **pending**.

---

## 🔍 Step-by-Step Verification Check

### Step 1: Check Sender Status in Brevo

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Settings** → **Senders** (left sidebar)
3. **Look for**: `project.clearpay@gmail.com` in the list
4. **Check the status**:
   - ✅ **"Verified"** (green) = Good! Should work
   - ⏳ **"Pending"** (yellow) = Still waiting for verification
   - ❌ **"Unverified"** (red) = Not verified
   - ❓ **Not in list** = Never added

### Step 2: If Not Verified or Not in List

**Add and Verify the Sender:**

1. **Click**: **"Add a sender"** button
2. **Enter**: `project.clearpay@gmail.com`
3. **Click**: **"Send verification email"**
4. **In the warning dialog**: Click **"Add this sender anyway"** (blue button)
5. **Check Gmail inbox** for `project.clearpay@gmail.com`
6. **Open the email from Brevo**
7. **Click the verification link**
8. **Wait 1-2 minutes** for status to update
9. **Refresh the Senders page** in Brevo
10. **Verify status shows "Verified"** ✅

### Step 3: If Status is "Pending"

**If verification is pending:**

1. **Check spam folder** in Gmail for verification email
2. **Resend verification** if needed:
   - Click on the sender in Brevo
   - Click **"Resend verification email"**
3. **Wait 5-10 minutes** for email to arrive
4. **Click verification link** in the email

### Step 4: After Verification

**Once verified:**

1. **Status should show "Verified"** ✅ in Brevo
2. **Wait 1-2 minutes** for changes to propagate
3. **Try sending test email again** from your app
4. **If still fails**: Check if Render needs redeploy

---

## 🚨 Common Issues

### Issue 1: Verification Email Not Received

**Solutions:**
- Check **Spam folder** in Gmail
- Check **All Mail** folder
- **Resend verification** from Brevo
- Wait 5-10 minutes (can be delayed)

### Issue 2: Verification Link Expired

**Solution:**
- Go to Brevo → Settings → Senders
- Click on the sender
- Click **"Resend verification email"**
- Use the new link (usually expires in 24 hours)

### Issue 3: Verified but Still Getting Error

**Possible causes:**
1. **Render cache**: Redeploy the service
2. **Verification not complete**: Check status in Brevo
3. **Wrong email**: Verify you're using `project.clearpay@gmail.com` exactly
4. **Propagation delay**: Wait 2-5 minutes after verification

**Solutions:**
- **Redeploy Render service**: Go to Render Dashboard → Your service → Manual Deploy
- **Check sender status** in Brevo again
- **Wait 2-5 minutes** after verification before testing

### Issue 4: Multiple Sender Entries

**If you see multiple entries:**
- Delete unverified ones
- Keep only the verified one
- Make sure the verified one is active

---

## ✅ Verification Checklist

Before testing, verify:

- [ ] Sender `project.clearpay@gmail.com` exists in Brevo → Settings → Senders
- [ ] Status shows **"Verified"** ✅ (not "Pending" or "Unverified")
- [ ] Verification email was clicked (check email history)
- [ ] Waited 1-2 minutes after verification
- [ ] SMTP credentials are correct in Render/environment
- [ ] Render service was redeployed after verification (if needed)

---

## 🔄 Render Deployment After Verification

**If you verified the sender but Render still shows error:**

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Select your service**: `clearpay-web-dev-k3h3`
3. **Click**: **"Manual Deploy"** → **"Deploy latest commit"**
4. **Wait for deployment** to complete (2-5 minutes)
5. **Try sending test email again**

**Why?** Render may have cached the old configuration or needs to refresh connections.

---

## 📧 Quick Test After Verification

1. **Verify sender is "Verified"** ✅ in Brevo
2. **Wait 2 minutes** for propagation
3. **Send test email** from your app
4. **Check Brevo dashboard**: Transactional → Real-time
5. **Should show "Sent"** (not "Error")

---

## 🆘 Still Not Working?

If sender is verified but still getting error:

1. **Double-check sender email** in your app matches exactly: `project.clearpay@gmail.com`
2. **Check Brevo sender list** - make sure it's there and verified
3. **Redeploy Render service** to clear any cache
4. **Wait 5 minutes** and try again
5. **Check Brevo logs**: Transactional → Real-time for detailed error

**Contact Brevo support** if sender is verified but emails still rejected.

