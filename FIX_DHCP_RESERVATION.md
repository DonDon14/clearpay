# Fix: PC IP Changed - DHCP Reservation Not Working

## Quick Fix: Update Reservation or Fix It

Your PC got `192.168.18.60` instead of `192.168.18.2`. Let's fix this.

---

## Option 1: Update Reservation to Current IP (Easiest)

Since your PC is already at `192.168.18.60`, just reserve that IP instead:

1. **In Router Admin**, go to **"DHCP Static IP"**
2. **Find your entry** (MAC: `00-E0-22-AD-A0-8D`)
3. **Update IP Address** to: `192.168.18.60`
4. **Click Apply**

**Then update all your configs:**
- Port forwarding: Change internal IP to `192.168.18.60`
- Backend baseURL: Keep as is (will work with any local IP)
- Flutter app: Keep as is (uses domain, not local IP)

---

## Option 2: Fix Reservation to Use 192.168.18.2 (Better)

If you want to keep using `.2`, we need to make sure the reservation works:

### Step 1: Release Current IP on PC

1. **On Server PC**, open **Command Prompt** (as Administrator)
2. **Type:**
   ```bash
   ipconfig /release
   ```
3. **Wait 10 seconds**

### Step 2: Verify Reservation in Router

1. **In Router Admin**, go to **"DHCP Static IP"**
2. **Check your entry:**
   - MAC Address: `00-E0-22-AD-A0-8D` ✓
   - IP Address: `192.168.18.2` ✓
3. **If missing or wrong, add/update it**

### Step 3: Renew IP on PC

1. **In Command Prompt**, type:**
   ```bash
   ipconfig /renew
   ```
2. **Check the IP:**
   ```bash
   ipconfig
   ```
3. **Should show:** `192.168.18.2` ✓

### Step 4: If Still Not Working

**Check DHCP Server Settings:**
1. Go to **"DHCP Server"** in router
2. **Make sure Start IP is NOT 192.168.18.2**
   - Should be `192.168.18.10` or higher
   - This frees up `.2` for reservation
3. **Click Apply**

**Then try again:**
- `ipconfig /release`
- Wait 10 seconds
- `ipconfig /renew`
- Check IP

---

## Option 3: Use Current IP (192.168.18.60) - Simplest

If you don't mind using `.60`, just update port forwarding:

1. **In Router Admin**, go to **"Forward Rules"** → **Port Forwarding**
2. **Find your ClearPay rule**
3. **Change Internal IP** from `192.168.18.2` to `192.168.18.60`
4. **Save/Apply**

**Update Reservation:**
1. Go to **"DHCP Static IP"**
2. **Update IP** to `192.168.18.60`
3. **Apply**

**Everything else stays the same!** The local IP doesn't matter for internet access - only port forwarding needs to match.

---

## Why This Happened

Possible reasons:
1. **DHCP reservation wasn't saved properly**
2. **PC got IP before reservation was set**
3. **DHCP Start IP conflicts with reservation**
4. **Router needs restart after setting reservation**

---

## Recommended: Use Current IP (192.168.18.60)

**Easiest solution:**
1. ✅ Update DHCP reservation to `192.168.18.60`
2. ✅ Update port forwarding to `192.168.18.60`
3. ✅ Everything else works the same!

**The local IP doesn't affect:**
- Internet access (uses public IP)
- Domain name (Dynamic DNS)
- Flutter app (uses domain, not local IP)

---

## Quick Fix Steps

1. **In Router:**
   - Go to "DHCP Static IP"
   - Update IP to `192.168.18.60`
   - Apply

2. **In Router:**
   - Go to "Forward Rules" → Port Forwarding
   - Update Internal IP to `192.168.18.60`
   - Apply

3. **On PC:**
   - Set network to "Automatic (DHCP)"
   - It will get `.60` from reservation

4. **Test:**
   - `ipconfig` should show `192.168.18.60`
   - Port forwarding should work
   - Internet access should work

**Done!** Your server will stay at `.60` now. 🎉

