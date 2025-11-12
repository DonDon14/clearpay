# Fix: PC Disconnected After Setting Static IP

## Quick Fix: Revert to Automatic (DHCP) First

**Step 1: Get Your Connection Back**

1. **Open Settings** (Win + I)
2. **Network & Internet** → **Ethernet** (or **Wi-Fi**)
3. **Click your network name**
4. **Scroll to "IP settings"**
5. **Click "Edit"** next to IP assignment
6. **Change from "Manual" back to "Automatic (DHCP)"**
7. **Click Save**

Your PC should reconnect to the network.

---

## Step 2: Find the Correct Network Settings

Before setting static IP, we need to know your router's actual settings:

1. **Open Command Prompt** (Win + R, type `cmd`, Enter)
2. **Type:** `ipconfig` and press Enter
3. **Look for these values:**
   - **IPv4 Address:** (e.g., 192.168.18.5) ← Your current IP
   - **Subnet Mask:** (e.g., 255.255.255.0)
   - **Default Gateway:** (e.g., 192.168.18.1) ← Your router IP
   - **DNS Servers:** (e.g., 192.168.18.1)

**Write these down!**

---

## Step 3: Check Router's DHCP Range

The static IP must be **outside** the router's DHCP range to avoid conflicts.

### Option A: Check Router Admin Panel

1. **Open browser**, go to your **Default Gateway** IP (from ipconfig)
   - Usually: `http://192.168.18.1`
2. **Login** to router admin
3. **Find DHCP settings** or **LAN settings**
4. **Check DHCP range:**
   - Example: `192.168.18.100` to `192.168.18.200`
   - This means IPs 1-99 and 201-254 are safe for static IPs

### Option B: Use an IP Outside Common Range

Most routers use:
- **DHCP Range:** 192.168.18.100 to 192.168.18.200
- **Safe Static IPs:** 192.168.18.2 to 192.168.18.99

**Your original IP `192.168.18.2` should be fine** - the issue might be something else.

---

## Step 4: Set Static IP Correctly

Once you have the correct info:

1. **Open Settings** → **Network & Internet**
2. **Click your network** → **Edit** IP settings
3. **Change to "Manual"**
4. **Turn IPv4 ON**
5. **Enter settings:**

   **IP address:** `192.168.18.2` (or another safe IP)
   
   **Subnet prefix length:** `24` (NOT 255.255.255.0!)
   
   **Gateway:** `192.168.18.1` (your router IP from ipconfig)
   
   **Preferred DNS:** `8.8.8.8` (Google) or your router IP
   
   **Alternate DNS:** `8.8.4.4` (Google) or leave empty

6. **Click Save**

---

## Common Issues & Fixes

### Issue 1: Wrong Gateway IP
- **Symptom:** Can't connect to internet
- **Fix:** Use the exact Gateway IP from `ipconfig`

### Issue 2: IP Conflict
- **Symptom:** "IP address conflict" error
- **Fix:** Try a different IP (e.g., 192.168.18.10, 192.168.18.50)

### Issue 3: Wrong Subnet
- **Symptom:** No connection at all
- **Fix:** Use prefix length `24` (for 255.255.255.0 subnet mask)

### Issue 4: Router Blocks Static IPs
- **Symptom:** Works but disconnects randomly
- **Fix:** Reserve the IP in router's DHCP reservation instead

---

## Alternative: Use DHCP Reservation (Easier & Safer)

Instead of static IP on PC, reserve the IP in your router:

1. **Access router admin** (http://192.168.18.1)
2. **Find "DHCP Reservation"** or "Static DHCP"
3. **Add reservation:**
   - **MAC Address:** (find in `ipconfig /all` - look for "Physical Address")
   - **IP Address:** 192.168.18.2
   - **Save**

**Benefits:**
- ✅ Router manages the IP
- ✅ No conflicts
- ✅ Easier to change later
- ✅ Still works for port forwarding

---

## Step-by-Step: Fix Your Current Situation

### Immediate Fix (Get Connected):

1. **Settings** → **Network & Internet**
2. **Your network** → **Edit IP settings**
3. **Change to "Automatic (DHCP)"**
4. **Save** → Wait for reconnection

### Then Set Static IP Properly:

1. **Run `ipconfig`** in Command Prompt
2. **Note your current:**
   - IPv4 Address
   - Default Gateway
   - Subnet Mask

3. **Settings** → **Network & Internet** → **Edit IP settings**
4. **Manual** → **IPv4 ON**
5. **Enter:**
   - **IP:** `192.168.18.2` (or try `.10`, `.50` if conflict)
   - **Subnet prefix:** `24`
   - **Gateway:** (from ipconfig - usually 192.168.18.1)
   - **DNS:** `8.8.8.8` and `8.8.4.4`
6. **Save**

---

## Verify It Works

After setting static IP:

1. **Open Command Prompt**
2. **Type:** `ipconfig`
3. **Check:**
   - IPv4 Address = 192.168.18.2 ✓
   - Default Gateway = 192.168.18.1 ✓
4. **Test internet:** `ping 8.8.8.8`
5. **Test router:** `ping 192.168.18.1`

If both work, you're good! ✅

---

## If Still Not Working

1. **Check router admin** - make sure nothing is blocking
2. **Try different IP** - maybe 192.168.18.10 or 192.168.18.50
3. **Use DHCP reservation** instead (easier)
4. **Check Windows Firewall** - make sure it's not blocking
5. **Restart network adapter:**
   - Settings → Network & Internet → Change adapter options
   - Right-click your adapter → Disable → Wait 10 sec → Enable

---

## Quick Checklist

- [ ] Reverted to Automatic (DHCP) - connection restored
- [ ] Ran `ipconfig` - got current network info
- [ ] Checked router DHCP range
- [ ] Set static IP outside DHCP range
- [ ] Used prefix length `24` (not 255.255.255.0)
- [ ] Used correct Gateway IP
- [ ] Tested connection with `ping`
- [ ] Verified internet works

**Most likely issue:** Wrong Gateway IP or Subnet prefix length. Double-check these from `ipconfig`!

