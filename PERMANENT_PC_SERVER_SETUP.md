# Permanent PC Server Setup - Make Your PC Accessible Online

Since you want to use your PC as the server permanently, here's the **best approach**:

## Recommended Solution: Port Forwarding + Dynamic DNS + SSL

This gives you:
- ✅ Permanent access (your PC stays the server)
- ✅ Stable URL (doesn't change)
- ✅ HTTPS (secure connection)
- ✅ Free (except domain if you want custom name)
- ✅ Full control

---

## Step 1: Set Static IP for Your Server PC

**Important:** Your server PC must have a static local IP so port forwarding always works.

1. **On Server PC**, open Network Settings:
   - Right-click network icon → "Open Network & Internet settings"
   - Click "Change adapter options"
   - Right-click your network adapter → "Properties"
   - Select "Internet Protocol Version 4 (TCP/IPv4)" → "Properties"

2. **Set Static IP:**
   - Select "Use the following IP address"
   - **IP Address:** `192.168.18.2` (your current IP)
   - **Subnet Mask:** `255.255.255.0`
   - **Default Gateway:** `192.168.18.1` (usually your router IP)
   - **DNS:** `8.8.8.8` and `8.8.4.4` (Google DNS)
   - Click OK

---

## Step 2: Find Your Router's Admin Panel

1. **Find Router IP:**
   - On server PC, open Command Prompt
   - Type: `ipconfig`
   - Look for "Default Gateway" (usually `192.168.18.1` or `192.168.1.1`)

2. **Access Router:**
   - Open browser, go to: `http://192.168.18.1` (or your gateway IP)
   - Login with admin credentials
   - (Check router label for default username/password)

---

## Step 3: Configure Port Forwarding

1. **Find Port Forwarding Section:**
   - Look for: "Port Forwarding", "Virtual Server", "NAT", or "Applications & Gaming"
   - Usually under "Advanced" or "Firewall"

2. **Add Port Forwarding Rule:**
   - **Service Name:** ClearPay
   - **External Port:** 80 (HTTP) or 8080 (if 80 is blocked by ISP)
   - **Internal IP:** 192.168.18.2 (your server PC)
   - **Internal Port:** 80
   - **Protocol:** TCP
   - **Status:** Enabled
   - **Save/Apply**

3. **Optional - Add HTTPS Port:**
   - **External Port:** 443 (HTTPS)
   - **Internal IP:** 192.168.18.2
   - **Internal Port:** 443
   - **Protocol:** TCP
   - (You'll set up HTTPS later)

---

## Step 4: Set Up Dynamic DNS (Free)

Since your public IP may change, use Dynamic DNS for a stable URL.

### Option A: DuckDNS (Free & Easy - Recommended)

1. **Sign up:** https://www.duckdns.org/
   - Free account
   - Choose a subdomain: `clearpay.duckdns.org`

2. **Get your token** from DuckDNS dashboard

3. **Install DuckDNS Updater on Server PC:**
   - Download: https://www.duckdns.org/install.jsp
   - Or use Windows Task Scheduler to update IP automatically

4. **Test:** Your site will be accessible at: `http://clearpay.duckdns.org`

### Option B: No-IP (Free)

1. **Sign up:** https://www.noip.com/
2. **Create hostname:** `clearpay.ddns.net`
3. **Install No-IP DUC client** on server PC
4. **Test:** `http://clearpay.ddns.net`

---

## Step 5: Configure Windows Firewall

1. **On Server PC**, open Windows Defender Firewall
2. **Allow Port 80:**
   - Click "Advanced settings"
   - "Inbound Rules" → "New Rule"
   - Select "Port" → Next
   - TCP, Port 80 → Next
   - Allow connection → Next
   - Check all profiles → Next
   - Name: "ClearPay HTTP" → Finish

3. **Allow Port 443 (for HTTPS later):**
   - Same process, but Port 443
   - Name: "ClearPay HTTPS"

---

## Step 6: Update Your Configuration

### Update Backend (`app/Config/App.php`):

```php
public string $baseURL = 'http://clearpay.duckdns.org/ClearPay/public/';
```

Or if using custom domain:
```php
public string $baseURL = 'http://yourdomain.com/ClearPay/public/';
```

### Update Flutter App (`flutter_app/lib/services/api_service.dart`):

```dart
// For online access
static const String serverIp = 'clearpay.duckdns.org'; // Your Dynamic DNS domain
// Or: static const String serverIp = 'yourdomain.com';

static String get baseUrl {
  if (kIsWeb) {
    return 'http://$serverIp$projectPath';
  } else {
    // For mobile - use your domain
    return 'http://$serverIp$projectPath';
  }
}
```

### Update CORS (`app/Config/Cors.php`):

Add your domain to `allowedOrigins`:
```php
'allowedOrigins' => [
    // ... existing origins ...
    'http://clearpay.duckdns.org',
    'http://clearpay.duckdns.org/ClearPay/public',
    // ... rest ...
],
```

---

## Step 7: Set Up HTTPS (SSL Certificate) - Recommended

For security, enable HTTPS using Let's Encrypt (free).

### Option A: Use Cloudflare (Easiest - Free SSL)

1. **Sign up:** https://www.cloudflare.com/
2. **Add your domain** (or DuckDNS domain)
3. **Change nameservers** to Cloudflare's
4. **Enable SSL/TLS** → "Full" mode
5. **Update baseURL to HTTPS:**
   ```php
   public string $baseURL = 'https://clearpay.duckdns.org/ClearPay/public/';
   ```

### Option B: Use Let's Encrypt with Certbot

1. **Install Certbot** on server PC
2. **Generate certificate:**
   ```bash
   certbot certonly --standalone -d clearpay.duckdns.org
   ```
3. **Configure Apache** to use SSL certificate
4. **Update baseURL to HTTPS**

---

## Step 8: Security Hardening

### 1. Change Default Passwords
- MySQL root password
- Admin account passwords
- Router admin password

### 2. Disable Unnecessary Services
- Only run Apache and MySQL
- Disable other XAMPP services if not needed

### 3. Regular Updates
- Keep Windows updated
- Keep XAMPP updated
- Keep PHP updated

### 4. Firewall Rules
- Only allow ports 80 and 443
- Block all other ports

### 5. Use Strong Passwords
- Database passwords
- Admin accounts
- Router access

---

## Step 9: Test Your Setup

1. **From another network** (mobile data, different WiFi):
   - Go to: `http://clearpay.duckdns.org/ClearPay/public/`
   - Should see ClearPay login page

2. **Test Flutter App:**
   - Update app with your domain
   - Build and test from anywhere
   - Should connect successfully

---

## Troubleshooting

### Can't Access from Internet
- ✅ Check port forwarding is configured correctly
- ✅ Check Windows Firewall allows ports 80/443
- ✅ Check router firewall settings
- ✅ Verify Dynamic DNS is updating
- ✅ Check if ISP blocks port 80 (try port 8080)

### Connection Timeout
- ✅ Verify server PC is running
- ✅ Check Apache is running
- ✅ Verify static IP is set correctly
- ✅ Check port forwarding rule is active

### SSL/HTTPS Issues
- ✅ Verify certificate is valid
- ✅ Check Apache SSL configuration
- ✅ Ensure port 443 is forwarded

---

## Alternative: Use ngrok for Quick Testing

If you want to test quickly before setting up port forwarding:

1. **Install ngrok** on server PC
2. **Run:** `ngrok http 80`
3. **Get URL:** `https://abc123.ngrok.io`
4. **Update configs** to use ngrok URL
5. **Test from anywhere**

**Note:** ngrok free tier gives you a new URL each time. For permanent solution, use port forwarding + Dynamic DNS.

---

## Summary: Best Setup for You

**Recommended Configuration:**
1. ✅ Static IP on server PC
2. ✅ Port forwarding (port 80 → 192.168.18.2:80)
3. ✅ Dynamic DNS (DuckDNS - free)
4. ✅ Cloudflare (free SSL/HTTPS)
5. ✅ Windows Firewall configured
6. ✅ Security hardening

**Result:**
- Your site accessible at: `https://clearpay.duckdns.org/ClearPay/public/`
- Works from anywhere in the world
- HTTPS secured
- Free (except if you want custom domain)
- Your PC stays the server

---

## Quick Start Checklist

- [ ] Set static IP on server PC (192.168.18.2)
- [ ] Configure port forwarding on router (port 80)
- [ ] Sign up for DuckDNS (get free domain)
- [ ] Install DuckDNS updater on server PC
- [ ] Configure Windows Firewall (allow port 80)
- [ ] Update backend baseURL to use domain
- [ ] Update Flutter app to use domain
- [ ] Test from external network
- [ ] Set up Cloudflare for HTTPS (optional but recommended)
- [ ] Security hardening (change passwords, etc.)

**That's it!** Your PC is now a permanent web server accessible from anywhere! 🎉

