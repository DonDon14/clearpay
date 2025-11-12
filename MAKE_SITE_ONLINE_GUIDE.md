# How to Access Your ClearPay Site Online

You have several options to make your local server accessible from the internet:

## Option 1: Use a Tunneling Service (Easiest - Recommended for Testing)

### Using ngrok (Free & Popular)

1. **Download ngrok**
   - Go to: https://ngrok.com/download
   - Download for Windows
   - Extract to a folder (e.g., `C:\ngrok\`)

2. **Sign up for free account**
   - Go to: https://dashboard.ngrok.com/signup
   - Get your authtoken

3. **Configure ngrok**
   ```bash
   ngrok config add-authtoken YOUR_AUTH_TOKEN
   ```

4. **Start the tunnel**
   ```bash
   ngrok http 192.168.18.2:80
   ```
   Or if accessing via ClearPay path:
   ```bash
   ngrok http 192.168.18.2:80 --host-header="192.168.18.2"
   ```

5. **Get your public URL**
   - ngrok will give you a URL like: `https://abc123.ngrok.io`
   - This URL is accessible from anywhere on the internet!

6. **Update your Flutter app**
   - Change `serverIp` in `flutter_app/lib/services/api_service.dart`:
   ```dart
   static const String serverIp = 'abc123.ngrok.io'; // Your ngrok URL
   ```
   - Update `baseUrl` to use HTTPS:
   ```dart
   return 'https://$serverIp/ClearPay/public';
   ```

**Pros:**
- ✅ Free (with limitations)
- ✅ Works immediately
- ✅ HTTPS included
- ✅ No router configuration needed

**Cons:**
- ❌ Free tier: URL changes each time you restart
- ❌ Free tier: Limited bandwidth
- ❌ Requires ngrok to be running

### Using Cloudflare Tunnel (Free & More Stable)

1. **Install cloudflared**
   - Download from: https://developers.cloudflare.com/cloudflare-one/connections/connect-apps/install-and-setup/installation/
   
2. **Create tunnel**
   ```bash
   cloudflared tunnel create clearpay
   ```

3. **Run tunnel**
   ```bash
   cloudflared tunnel --url http://192.168.18.2:80
   ```

**Pros:**
- ✅ Free
- ✅ More stable than ngrok
- ✅ HTTPS included

**Cons:**
- ❌ URL changes each time (unless you configure domain)

---

## Option 2: Port Forwarding (Permanent Solution)

This makes your server accessible directly via your public IP.

### Step 1: Find Your Public IP
1. On server PC, go to: https://whatismyipaddress.com/
2. Note your public IP address (e.g., `123.45.67.89`)

### Step 2: Configure Router Port Forwarding

1. **Access Router Admin Panel**
   - Usually: `http://192.168.1.1` or `http://192.168.0.1`
   - Check router label for default IP
   - Login with admin credentials

2. **Find Port Forwarding Section**
   - Look for: "Port Forwarding", "Virtual Server", or "NAT"
   - Usually under "Advanced" or "Firewall" settings

3. **Add Port Forwarding Rule**
   - **Service Name:** ClearPay
   - **External Port:** 80 (or 8080 if 80 is blocked)
   - **Internal IP:** 192.168.18.2 (your server PC)
   - **Internal Port:** 80
   - **Protocol:** TCP
   - **Save/Apply**

4. **Set Static IP for Server PC** (Important!)
   - On server PC, set a static IP so it doesn't change
   - See "Step 6" in LOCAL_SERVER_SETUP_GUIDE.md

### Step 3: Update Configuration

1. **Update `app/Config/App.php`**
   ```php
   public string $baseURL = 'http://YOUR_PUBLIC_IP/ClearPay/public/';
   ```
   Or if using a domain:
   ```php
   public string $baseURL = 'http://yourdomain.com/ClearPay/public/';
   ```

2. **Update Flutter App**
   ```dart
   static const String serverIp = 'YOUR_PUBLIC_IP'; // or 'yourdomain.com'
   ```

3. **Update CORS** (`app/Config/Cors.php`)
   - Add your public IP or domain to `allowedOrigins`

### Step 4: Configure Windows Firewall
- Allow port 80 (or your chosen port) through Windows Firewall on server PC

**Pros:**
- ✅ Permanent solution
- ✅ Direct access
- ✅ No third-party service needed

**Cons:**
- ❌ Requires router access
- ❌ Security risk (exposes server to internet)
- ❌ Public IP may change (use Dynamic DNS)
- ❌ No HTTPS (unless you set up SSL)

---

## Option 3: Dynamic DNS (For Port Forwarding)

If your public IP changes, use Dynamic DNS:

1. **Sign up for Dynamic DNS service**
   - Free options: No-IP, DuckDNS, Dynu
   - Example: https://www.noip.com/

2. **Get a domain name**
   - Example: `clearpay.ddns.net`

3. **Install Dynamic DNS client on server PC**
   - Keeps your domain pointing to current IP

4. **Use domain instead of IP**
   - Update all configs to use: `clearpay.ddns.net`

---

## Option 4: Deploy to Cloud Hosting (Best for Production)

For a production-ready solution, deploy to:

### Free Options:
- **InfinityFree** (what you were using before)
- **000webhost**
- **Heroku** (limited free tier)

### Paid Options (Recommended for Production):
- **DigitalOcean** ($5/month)
- **Linode** ($5/month)
- **AWS EC2** (pay as you go)
- **Vultr** ($5/month)

These provide:
- ✅ Stable URLs
- ✅ Better security
- ✅ SSL certificates
- ✅ Better performance
- ✅ Professional setup

---

## Security Warnings ⚠️

**If exposing your local server to the internet:**

1. **Change default passwords**
   - MySQL root password
   - Admin account passwords

2. **Use HTTPS** (SSL certificate)
   - Free: Let's Encrypt
   - Or use Cloudflare (free SSL)

3. **Firewall rules**
   - Only allow necessary ports
   - Block unnecessary access

4. **Keep software updated**
   - XAMPP
   - PHP
   - MySQL

5. **Regular backups**
   - Database
   - Files

---

## Recommended Approach

**For Testing/Development:**
- Use **ngrok** or **Cloudflare Tunnel** (easiest, quick setup)

**For Production:**
- Deploy to **cloud hosting** (DigitalOcean, Vultr, etc.)
- Or use **port forwarding + Dynamic DNS** if you want to keep local server

---

## Quick Start: ngrok Setup

1. Download ngrok: https://ngrok.com/download
2. Sign up: https://dashboard.ngrok.com/signup
3. Get authtoken from dashboard
4. Run:
   ```bash
   ngrok config add-authtoken YOUR_TOKEN
   ngrok http 192.168.18.2:80
   ```
5. Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)
6. Update Flutter app to use this URL
7. Update backend `baseURL` to use this URL

**That's it!** Your site is now accessible online! 🎉

