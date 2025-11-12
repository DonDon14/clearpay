# Next Steps After Setting Static IP

## ✅ Step 1: Set PC Network to Automatic (DHCP)

Your router now reserves `192.168.18.2` for your PC, so set your PC to get IP automatically:

1. **On Server PC**, open **Settings** (Win + I)
2. **Network & Internet** → **Ethernet** (or **Wi-Fi**)
3. **Click your network name**
4. **Edit IP settings**
5. **Change to "Automatic (DHCP)"**
6. **Save**

Your PC will now get `192.168.18.2` automatically from the router.

**Verify it worked:**
- Open Command Prompt
- Type: `ipconfig`
- Check that IPv4 Address = `192.168.18.2` ✓

---

## ✅ Step 2: Configure Port Forwarding

This makes your server accessible from the internet.

1. **In Router Admin**, go to **"Forward Rules"** (in left menu)
2. **Look for:** "Port Forwarding", "Virtual Server", or "NAT"
3. **Add New Rule:**
   - **Service Name:** ClearPay
   - **External Port:** 80 (HTTP) or 8080 (if 80 is blocked by ISP)
   - **Internal IP:** 192.168.18.2
   - **Internal Port:** 80
   - **Protocol:** TCP
   - **Status:** Enabled
   - **Save/Apply**

**Optional - Add HTTPS Port:**
- **External Port:** 443
- **Internal IP:** 192.168.18.2
- **Internal Port:** 443
- **Protocol:** TCP

---

## ✅ Step 3: Configure Windows Firewall

Allow incoming connections on your server PC:

1. **On Server PC**, open **Windows Defender Firewall**
2. **Advanced settings** → **Inbound Rules** → **New Rule**
3. **Select "Port"** → Next
4. **TCP**, Port **80** → Next
5. **Allow the connection** → Next
6. **Check all profiles** → Next
7. **Name:** "ClearPay HTTP" → Finish

**Repeat for Port 443** (if you set up HTTPS):
- Same steps, but Port **443**
- Name: "ClearPay HTTPS"

---

## ✅ Step 4: Set Up Dynamic DNS (Free)

Since your public IP may change, use Dynamic DNS for a stable URL.

### Option A: DuckDNS (Recommended - Free & Easy)

1. **Sign up:** https://www.duckdns.org/
   - Click "Sign in with Google" or create account
   - Free, no credit card needed

2. **Create subdomain:**
   - Choose: `clearpay` (or any name)
   - Your domain will be: `clearpay.duckdns.org`
   - Click "Add domain"

3. **Get your token** (shown on dashboard)

4. **Install DuckDNS Updater on Server PC:**
   - Download: https://www.duckdns.org/install.jsp
   - Or use Windows Task Scheduler to update automatically
   - Or manually update from browser: `https://www.duckdns.org/update?domains=clearpay&token=YOUR_TOKEN&ip=`

5. **Test:** Your site will be accessible at `http://clearpay.duckdns.org`

### Option B: No-IP (Alternative)

1. **Sign up:** https://www.noip.com/
2. **Create hostname:** `clearpay.ddns.net`
3. **Install No-IP DUC client** on server PC
4. **Test:** `http://clearpay.ddns.net`

---

## ✅ Step 5: Find Your Public IP (For Testing)

1. **On Server PC**, open browser
2. **Go to:** https://whatismyipaddress.com/
3. **Note your public IP** (e.g., `123.45.67.89`)

You can test with this IP first, then switch to Dynamic DNS domain.

---

## ✅ Step 6: Update Backend Configuration

### Update `app/Config/App.php` on Server PC:

```php
public string $baseURL = 'http://clearpay.duckdns.org/ClearPay/public/';
```

Or if using public IP for now:
```php
public string $baseURL = 'http://YOUR_PUBLIC_IP/ClearPay/public/';
```

### Update `.env` file on Server PC:

```env
app.baseURL = 'http://clearpay.duckdns.org/ClearPay/public/'
```

### Update CORS (`app/Config/Cors.php`):

Add your domain/IP to `allowedOrigins`:
```php
'allowedOrigins' => [
    // ... existing ...
    'http://clearpay.duckdns.org',
    'http://clearpay.duckdns.org/ClearPay/public',
    'http://YOUR_PUBLIC_IP',
    // ... rest ...
],
```

---

## ✅ Step 7: Update Flutter App

### Update `flutter_app/lib/services/api_service.dart`:

```dart
// For online access
static const String serverIp = 'clearpay.duckdns.org'; // Your Dynamic DNS domain
// Or: static const String serverIp = 'YOUR_PUBLIC_IP'; // If not using DNS yet

static String get baseUrl {
  if (kIsWeb) {
    return 'http://$serverIp$projectPath';
  } else {
    // For mobile - use your domain
    return 'http://$serverIp$projectPath';
  }
}
```

---

## ✅ Step 8: Test Your Setup

### Test 1: From Server PC Itself
1. Open browser on server PC
2. Go to: `http://localhost/ClearPay/public/`
3. Should see ClearPay login page ✓

### Test 2: From Another Device on Same Network
1. On your main PC or phone (same WiFi)
2. Go to: `http://192.168.18.2/ClearPay/public/`
3. Should see ClearPay login page ✓

### Test 3: From Internet (After Port Forwarding)
1. **Disconnect from WiFi** (use mobile data)
2. Go to: `http://YOUR_PUBLIC_IP/ClearPay/public/`
   - Or: `http://clearpay.duckdns.org/ClearPay/public/`
3. Should see ClearPay login page ✓

### Test 4: Flutter App
1. Update Flutter app with your domain/IP
2. Build and test from anywhere
3. Should connect successfully ✓

---

## ✅ Step 9: Set Up HTTPS (Optional but Recommended)

For security, enable HTTPS using Cloudflare (free):

1. **Sign up:** https://www.cloudflare.com/
2. **Add your domain** (clearpay.duckdns.org)
3. **Change nameservers** to Cloudflare's
4. **Enable SSL/TLS** → "Full" mode
5. **Update baseURL to HTTPS:**
   ```php
   public string $baseURL = 'https://clearpay.duckdns.org/ClearPay/public/';
   ```

---

## Troubleshooting

### Can't Access from Internet
- ✅ Check port forwarding is configured
- ✅ Check Windows Firewall allows port 80
- ✅ Check router firewall settings
- ✅ Verify Dynamic DNS is updating
- ✅ Check if ISP blocks port 80 (try port 8080)

### Connection Timeout
- ✅ Verify server PC is running
- ✅ Check Apache is running
- ✅ Verify static IP is working (`ipconfig`)
- ✅ Check port forwarding rule is active

### "This site can't be reached"
- ✅ Check public IP is correct
- ✅ Verify port forwarding
- ✅ Check Windows Firewall
- ✅ Test from same network first

---

## Quick Checklist

- [ ] PC network set to Automatic (DHCP) - gets 192.168.18.2
- [ ] Port forwarding configured (port 80 → 192.168.18.2:80)
- [ ] Windows Firewall allows port 80
- [ ] Dynamic DNS set up (DuckDNS or No-IP)
- [ ] Backend baseURL updated
- [ ] CORS updated with domain/IP
- [ ] Flutter app updated with domain/IP
- [ ] Tested from local network
- [ ] Tested from internet
- [ ] HTTPS set up (optional)

---

## Next: Start with Port Forwarding

**Your immediate next step:** Configure port forwarding in your router!

1. Go to **"Forward Rules"** in router menu
2. Add port forwarding rule
3. Test from internet

Then set up Dynamic DNS for a stable URL.

**You're almost there!** 🎉

