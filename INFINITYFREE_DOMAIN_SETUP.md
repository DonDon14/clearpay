# InfinityFree Domain Setup Guide
## Setting up clearpay.fwh.is on InfinityFree

This guide walks you through setting up your domain on InfinityFree from scratch.

---

## 📋 Prerequisites

- InfinityFree account created
- Domain `clearpay.fwh.is` purchased from a registrar
- Access to domain registrar's DNS management

---

## 🔧 Step 1: Add Domain to InfinityFree

1. **Log into InfinityFree:**
   - Go to [infinityfree.net](https://infinityfree.net)
   - Log into your account

2. **Add Domain:**
   - Go to "Control Panel" → "Domains"
   - Click "Add Domain"
   - Enter: `clearpay.fwh.is`
   - Click "Add Domain"

3. **Wait for Domain Activation:**
   - Domain may take a few minutes to activate
   - Check domain status in control panel
   - Status should show "Active"

---

## 🌐 Step 2: Configure DNS

You have **two options** for DNS configuration:

### Option A: Use InfinityFree Nameservers (Recommended)

**Steps:**
1. **Get InfinityFree Nameservers:**
   - In InfinityFree control panel, go to domain settings
   - Note the nameservers (usually `ns1.epizy.com` and `ns2.epizy.com`)

2. **Update Nameservers at Domain Registrar:**
   - Log into your domain registrar (where you bought `clearpay.fwh.is`)
   - Go to DNS/Nameserver settings
   - Change nameservers to InfinityFree's nameservers
   - Save changes

3. **Wait for Propagation:**
   - DNS changes take 24-48 hours to propagate
   - Check propagation: [whatsmydns.net](https://www.whatsmydns.net)

### Option B: Use A Record (Advanced)

**Steps:**
1. **Get InfinityFree IP Address:**
   - Contact InfinityFree support or check control panel
   - Note the IP address

2. **Add A Record at Domain Registrar:**
   - Log into domain registrar
   - Go to DNS management
   - Add A record:
     - Type: A
     - Name: @ (or blank, or `clearpay`)
     - Value: InfinityFree's IP address
     - TTL: 3600
   - Save changes

3. **Wait for Propagation:**
   - DNS changes take 24-48 hours to propagate

---

## 🔒 Step 3: Enable SSL Certificate

1. **Enable SSL in InfinityFree:**
   - Go to domain settings in InfinityFree control panel
   - Find SSL/HTTPS settings
   - Click "Enable SSL" or "Request SSL Certificate"
   - Wait 5-10 minutes for SSL to activate

2. **Verify SSL:**
   - Try accessing: `https://clearpay.fwh.is`
   - Browser should show secure padlock
   - HTTP should redirect to HTTPS

---

## 📤 Step 4: Upload Files

1. **Access File Manager:**
   - In InfinityFree control panel, go to "File Manager"
   - Navigate to `htdocs/` folder (this is your document root)

2. **Upload Files:**
   - Upload all files following the flat structure guide
   - Files should be in `htdocs/` root, not in subfolders
   - Refer to `INFINITYFREE_QUICK_START.md` for file structure

3. **Set Permissions:**
   - Files: `644`
   - Folders: `755`
   - `writable/` folder: `775` (recursive)

---

## ✅ Step 5: Verify Setup

1. **Check DNS Resolution:**
   ```bash
   nslookup clearpay.fwh.is
   ```
   Should show InfinityFree's IP address

2. **Test Connection:**
   ```bash
   ping clearpay.fwh.is
   ```
   Should respond (if ping is allowed)

3. **Test Website:**
   - Open browser
   - Go to: `https://clearpay.fwh.is`
   - Should load your website (or show CodeIgniter error if config needed)

---

## 🐛 Troubleshooting

### Domain Not Resolving

**Symptoms:** `ERR_CONNECTION_TIMED_OUT` or domain doesn't resolve

**Solutions:**
1. Verify domain is added to InfinityFree account
2. Check DNS records are correct
3. Wait 24-48 hours after DNS changes
4. Check domain registrar for any issues

### SSL Not Working

**Symptoms:** Browser shows "Not Secure" or SSL error

**Solutions:**
1. Enable SSL in InfinityFree control panel
2. Wait 5-10 minutes for SSL activation
3. Clear browser cache
4. Try accessing via HTTP first, then HTTPS

### Files Not Loading

**Symptoms:** Website loads but shows errors

**Solutions:**
1. Verify files are in `htdocs/` folder
2. Check `index.php` exists in root
3. Verify file permissions
4. Check `.env` file is configured correctly

---

## ⏱️ Timeline

- **Domain Activation:** 5-10 minutes
- **DNS Propagation:** 24-48 hours (after changing DNS)
- **SSL Activation:** 5-10 minutes
- **Total Setup Time:** 1-2 days (mostly waiting for DNS)

---

## 📝 Checklist

- [ ] Domain added to InfinityFree account
- [ ] DNS configured (nameservers or A record)
- [ ] SSL certificate enabled
- [ ] Files uploaded to `htdocs/` folder
- [ ] File permissions set correctly
- [ ] `.env` file created with correct settings
- [ ] DNS propagated (check with nslookup)
- [ ] Website accessible via HTTPS

---

## 🆘 Still Having Issues?

1. **Check InfinityFree Status:**
   - Visit InfinityFree status page
   - Check for any service outages

2. **Test Temporary URL:**
   - Use InfinityFree's temporary URL to test files
   - If temporary URL works, issue is DNS-related

3. **Contact Support:**
   - InfinityFree Forum: [forum.infinityfree.com](https://forum.infinityfree.com)
   - Domain Registrar Support (for DNS issues)

---

**Remember:** The connection timeout error is almost always a DNS/domain configuration issue, not a CodeIgniter problem. Fix the domain setup first, then worry about application configuration.







