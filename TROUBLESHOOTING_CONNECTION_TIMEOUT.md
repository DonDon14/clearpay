# Troubleshooting Connection Timeout Error
## ERR_CONNECTION_TIMED_OUT on clearpay.fwh.is

This error means your browser cannot reach the server at all. This is **not a CodeIgniter configuration issue** - it's a server/domain connectivity problem.

---

## 🔍 Diagnosis Steps

### Step 1: Check Domain DNS

**Test if domain is pointing to InfinityFree:**

1. **Check DNS Records:**
   - Go to your domain registrar (where you bought `clearpay.fwh.is`)
   - Verify DNS records point to InfinityFree
   - Usually needs to point to InfinityFree's nameservers or IP

2. **Test DNS Resolution:**
   ```bash
   # In Command Prompt or Terminal
   nslookup clearpay.fwh.is
   # or
   ping clearpay.fwh.is
   ```
   
   **Expected:** Should show InfinityFree's IP address
   **If fails:** Domain DNS is not configured correctly

---

### Step 2: Verify InfinityFree Account Setup

1. **Log into InfinityFree Control Panel:**
   - Go to [infinityfree.net](https://infinityfree.net)
   - Log into your account

2. **Check if Domain is Added:**
   - Go to "Domains" section
   - Verify `clearpay.fwh.is` is added to your account
   - Check domain status (should be "Active")

3. **Check Domain Configuration:**
   - Domain should be linked to a hosting account
   - SSL certificate should be active (may take time to activate)

---

### Step 3: Verify InfinityFree Nameservers

**If using InfinityFree nameservers:**

1. **Get InfinityFree Nameservers:**
   - In InfinityFree control panel, check what nameservers to use
   - Usually something like: `ns1.epizy.com` and `ns2.epizy.com`

2. **Update Domain Registrar:**
   - Go to your domain registrar (where you bought `clearpay.fwh.is`)
   - Update nameservers to InfinityFree's nameservers
   - Wait 24-48 hours for DNS propagation

---

### Step 4: Check if Files are Uploaded

1. **Access InfinityFree File Manager:**
   - Log into InfinityFree control panel
   - Go to "File Manager"
   - Navigate to `htdocs/` folder

2. **Verify Files Exist:**
   - Check if `index.php` exists
   - Check if `app/` folder exists
   - Check if `.htaccess` exists

---

### Step 5: Test Alternative Access Methods

1. **Try InfinityFree Temporary URL:**
   - InfinityFree provides a temporary URL for testing
   - Check in control panel: Usually `yourusername.epizy.com` or similar
   - Test if this URL works first

2. **Try IP Address:**
   - If you know InfinityFree's IP, try accessing via IP
   - This helps determine if it's a DNS issue

---

## 🔧 Common Solutions

### Solution 1: Domain Not Added to InfinityFree

**Problem:** Domain not registered in InfinityFree account

**Fix:**
1. Log into InfinityFree control panel
2. Go to "Domains" → "Add Domain"
3. Enter `clearpay.fwh.is`
4. Follow setup wizard
5. Wait for domain to activate (may take a few minutes)

---

### Solution 2: DNS Not Configured Correctly

**Problem:** Domain DNS records don't point to InfinityFree

**Fix:**
1. **Option A: Use InfinityFree Nameservers (Recommended)**
   - Get nameservers from InfinityFree control panel
   - Update nameservers at your domain registrar
   - Wait 24-48 hours for propagation

2. **Option B: Use A Record**
   - Get InfinityFree's IP address from control panel
   - Add A record at domain registrar:
     - Type: A
     - Name: @ (or blank)
     - Value: InfinityFree's IP address
     - TTL: 3600

---

### Solution 3: DNS Propagation Delay

**Problem:** DNS changes take time to propagate

**Fix:**
- Wait 24-48 hours after changing DNS
- Check DNS propagation: [whatsmydns.net](https://www.whatsmydns.net)
- Enter `clearpay.fwh.is` and check if it shows InfinityFree's IP globally

---

### Solution 4: SSL Certificate Not Activated

**Problem:** SSL certificate not yet active (InfinityFree uses Let's Encrypt)

**Fix:**
1. In InfinityFree control panel, enable SSL for domain
2. Wait 5-10 minutes for SSL to activate
3. Try accessing via HTTPS: `https://clearpay.fwh.is`
4. Also try HTTP: `http://clearpay.fwh.is` (should redirect to HTTPS)

---

### Solution 5: Account Suspended or Inactive

**Problem:** InfinityFree account may be suspended or inactive

**Fix:**
1. Check InfinityFree control panel for any warnings
2. Verify account is active
3. Check if you've exceeded resource limits
4. Contact InfinityFree support if needed

---

### Solution 6: Files Not Uploaded Correctly

**Problem:** Files not uploaded or in wrong location

**Fix:**
1. Verify files are in `htdocs/` folder (not a subfolder)
2. Check `index.php` exists in root of `htdocs/`
3. Verify file permissions are correct
4. Check `.htaccess` file exists

---

## 🧪 Testing Checklist

Use this checklist to diagnose:

- [ ] Domain is added to InfinityFree account
- [ ] DNS records point to InfinityFree (check with `nslookup`)
- [ ] Nameservers are correct (if using InfinityFree nameservers)
- [ ] SSL certificate is active (check in control panel)
- [ ] Files are uploaded to `htdocs/` folder
- [ ] `index.php` exists in `htdocs/` root
- [ ] InfinityFree account is active (not suspended)
- [ ] Waited 24-48 hours after DNS changes (if recently changed)

---

## 🔍 Quick Diagnostic Commands

**Test DNS Resolution:**
```bash
nslookup clearpay.fwh.is
```

**Test Connection:**
```bash
ping clearpay.fwh.is
```

**Test HTTP Connection:**
```bash
curl -I http://clearpay.fwh.is
```

**Test HTTPS Connection:**
```bash
curl -I https://clearpay.fwh.is
```

---

## 📞 Next Steps

1. **Check InfinityFree Control Panel:**
   - Verify domain is added and active
   - Check SSL certificate status
   - Verify files are uploaded

2. **Check Domain Registrar:**
   - Verify DNS records are correct
   - Check nameservers (if using InfinityFree nameservers)
   - Verify domain is not expired

3. **Wait for DNS Propagation:**
   - If you just changed DNS, wait 24-48 hours
   - Use [whatsmydns.net](https://www.whatsmydns.net) to check propagation

4. **Test Alternative URL:**
   - Try InfinityFree's temporary URL first
   - If temporary URL works, issue is with domain DNS
   - If temporary URL doesn't work, issue is with InfinityFree account/files

---

## ⚠️ Important Notes

1. **DNS Propagation:** Can take up to 48 hours after changing DNS records
2. **SSL Activation:** InfinityFree's free SSL may take a few minutes to activate
3. **Account Limits:** Check if you've exceeded InfinityFree's resource limits
4. **File Location:** Files MUST be in `htdocs/` folder (document root)

---

## 🆘 Still Not Working?

If none of these solutions work:

1. **Contact InfinityFree Support:**
   - Use their forum: [forum.infinityfree.com](https://forum.infinityfree.com)
   - Check their knowledge base
   - Post your issue with details

2. **Verify Domain Status:**
   - Check if domain is expired
   - Verify domain is not blocked
   - Check domain registrar for any issues

3. **Try Temporary URL:**
   - Use InfinityFree's temporary URL to test if files work
   - If temporary URL works, problem is definitely DNS/domain related

---

**Remember:** `ERR_CONNECTION_TIMED_OUT` means the browser can't reach the server at all. This is **not a CodeIgniter issue** - it's a server/domain connectivity problem that needs to be fixed at the InfinityFree/DNS level first.




