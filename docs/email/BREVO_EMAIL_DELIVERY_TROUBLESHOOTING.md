# 🔍 Brevo Email Delivery Troubleshooting

## ⚠️ Common Error: "Sender Not Valid"

**Error Message**: `"Sending has been rejected because the sender you used project.clearpay@gmail.com is not valid. Validate your sender or authenticate your domain."`

**This means**: The sender email address is **not verified** in Brevo, or verification is **pending**.

### ✅ Solution: Verify Sender Email

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Settings** → **Senders** (in left sidebar)
3. **Check if sender exists**:
   - Look for `project.clearpay@gmail.com` in the list
   - Check status: Should show **"Verified"** ✅
   - If **"Pending"**: Check email and click verification link
   - If **"Unverified"** or **not in list**: Add it (see below)

4. **If sender doesn't exist or is unverified**:
   - Click **"Add a sender"**
   - Enter: `project.clearpay@gmail.com`
   - Click **"Send verification email"**
   - In warning dialog: Click **"Add this sender anyway"** (blue button)
   - Check Gmail inbox for `project.clearpay@gmail.com`
   - Click the verification link in the email from Brevo
   - Wait 1-2 minutes for status to update to "Verified" ✅

5. **After verification**:
   - **Wait 2-5 minutes** for changes to propagate
   - **Redeploy Render service** (if error persists):
     - Go to Render Dashboard → Your service
     - Click **"Manual Deploy"** → **"Deploy latest commit"**
   - Try sending test email again

**👉 See `BREVO_SENDER_VERIFICATION_CHECKLIST.md` for detailed troubleshooting**

---

## ✅ Good News: SMTP Connection is Working!

If Brevo shows the email was sent and you got no errors, **the SMTP configuration is correct**. The issue is likely with email delivery.

---

## 📧 Where to Check for Your Email

### 1. Check Spam/Junk Folder ⚠️ **MOST COMMON**

**Gmail:**
- Go to **Spam** folder (left sidebar)
- Look for emails from `project.clearpay@gmail.com` or `ClearPay`
- If found, click **"Not spam"** to move it to inbox

**Other Email Providers:**
- Check **Junk** or **Spam** folder
- Check **Promotions** tab (Gmail) - emails might go there

### 2. Check Brevo Dashboard for Delivery Status

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Transactional** → **Emails** (or **Statistics**)
3. **Check the email status**:
   - ✅ **Delivered**: Email reached the recipient's server
   - ⏳ **Pending**: Still being processed
   - ❌ **Bounced**: Email was rejected
   - ⚠️ **Opened**: Email was opened (if tracking enabled)

### 3. Verify "From" Email Address

**Important**: The "From" email (`project.clearpay@gmail.com`) must be:
- ✅ A valid Gmail address
- ✅ Not blocked by Gmail
- ⚠️ **May be treated as spam** if not verified

**Brevo allows Gmail addresses**, but Gmail may flag them as spam if:
- The sender reputation is low
- The email looks like spam
- The recipient doesn't recognize the sender

---

## 🔧 Solutions

### Solution 1: Check Spam Folder First

**This is the #1 reason emails don't appear in inbox!**

1. **Open Gmail**
2. **Click "Spam"** in the left sidebar
3. **Search for**: `project.clearpay` or `ClearPay`
4. **If found**:
   - Open the email
   - Click **"Not spam"**
   - Future emails should go to inbox

### Solution 2: Add Sender to Contacts

1. **Open the email** (even if in spam)
2. **Click the sender name** (`project.clearpay@gmail.com`)
3. **Add to contacts**
4. **Future emails** should go directly to inbox

### Solution 3: Check Brevo Statistics

1. **Login to Brevo**: https://app.brevo.com
2. **Go to**: **Transactional** → **Statistics** or **Emails**
3. **Find your test email**:
   - Check **Status**: Delivered, Bounced, or Pending
   - Check **Delivery time**
   - Check **Bounce reason** (if bounced)

### Solution 4: Verify Email Address

**Make sure you're checking the correct email:**
- The email you entered in the test form
- Check all email accounts if you have multiple
- Check if email was sent to a different address

### Solution 5: Wait a Few Minutes

**Email delivery can take time:**
- Usually instant, but can take 1-5 minutes
- Check again after a few minutes
- Some email providers delay unknown senders

### Solution 6: Check Email Headers (Advanced)

If you find the email in spam:
1. **Open the email**
2. **Click "Show original"** or **"View source"**
3. **Check headers** for:
   - `SPF`: Should show Brevo's servers
   - `DKIM`: Should be signed by Brevo
   - `DMARC`: Should pass

---

## 🚨 Common Issues

### Issue 1: Email Goes to Spam

**Cause**: Gmail doesn't recognize the sender

**Solution**:
- Mark as "Not spam" when you find it
- Add sender to contacts
- Use a custom domain (requires domain verification)

### Issue 2: Email Bounced

**Check Brevo dashboard** for bounce reason:
- **Invalid email**: Email address doesn't exist
- **Mailbox full**: Recipient's inbox is full
- **Blocked**: Recipient's server blocked the email

### Issue 3: Email Not Delivered

**Check**:
- Email address is correct
- Recipient's email server is working
- No firewall blocking

---

## ✅ Verification Checklist

- [ ] Checked **Spam/Junk** folder
- [ ] Checked **Promotions** tab (Gmail)
- [ ] Checked **All Mail** (Gmail)
- [ ] Verified email address is correct
- [ ] Checked Brevo dashboard for delivery status
- [ ] Waited 5 minutes for delivery
- [ ] Added sender to contacts (if found in spam)

---

## 📊 Brevo Dashboard - Where to Check

1. **Transactional Emails**:
   - Go to: **Transactional** → **Emails**
   - See all sent emails with status

2. **Statistics**:
   - Go to: **Statistics** → **Transactional**
   - See delivery rates, bounces, opens

3. **Email Logs**:
   - Go to: **Transactional** → **Logs**
   - See detailed logs for each email

---

## 💡 Pro Tips

1. **Always check spam first** - 90% of "missing" emails are in spam
2. **Add sender to contacts** - Prevents future spam filtering
3. **Use a custom domain** - Better deliverability (requires domain setup)
4. **Check Brevo dashboard** - Most accurate delivery status
5. **Test with multiple email addresses** - Gmail, Outlook, etc.

---

## 🎯 Next Steps

1. **Check your spam folder** right now
2. **Check Brevo dashboard** for delivery status
3. **If in spam**: Mark as "Not spam" and add to contacts
4. **If bounced**: Check bounce reason in Brevo
5. **If pending**: Wait a few minutes and check again

---

## 📞 Still Not Working?

If email is:
- ✅ **Delivered** in Brevo but not in inbox → Check spam folder
- ❌ **Bounced** in Brevo → Check bounce reason
- ⏳ **Pending** in Brevo → Wait and check again
- ❓ **Not showing in Brevo** → Check if email was actually sent

**Check the application logs** for any errors:
- `writable/logs/log-YYYY-MM-DD.log`
- Look for email-related errors

