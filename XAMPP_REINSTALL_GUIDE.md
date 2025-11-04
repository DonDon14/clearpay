# XAMPP Reinstall Guide - Keep Your Data Safe

## Step 1: Backup Important Files (DO THIS FIRST!)

### Backup Location:
Create a folder: `C:\xampp_backup`

### What to Backup:

#### 1. **MySQL Databases**
- **Method 1 (phpMyAdmin):**
  1. Open `http://localhost/phpmyadmin/`
  2. Click "Export" tab at the top
  3. Select "Quick" method
  4. Select all databases OR select your specific database
  5. Click "Go" to download SQL file
  6. Save it to `C:\xampp_backup\databases.sql`

- **Method 2 (Command Line):**
  ```
  C:\xampp\mysql\bin\mysqldump.exe --user=root --all-databases > C:\xampp_backup\all_databases.sql
  ```

#### 2. **Your Project Files (htdocs)**
- Copy entire folder: `C:\xampp\htdocs\ClearPay` 
- Paste to: `C:\xampp_backup\htdocs_backup\ClearPay`
- Copy any other projects you have in htdocs

#### 3. **phpMyAdmin Configuration**
- Copy: `C:\xampp\phpMyAdmin\config.inc.php`
- Paste to: `C:\xampp_backup\config.inc.php`

#### 4. **MySQL Data Directory (Full Backup)**
- Copy: `C:\xampp\mysql\data`
- Paste entire folder to: `C:\xampp_backup\mysql_data_backup`
- **This contains all your databases and is the safest backup!**

---

## Step 2: Stop XAMPP Services

1. Open **XAMPP Control Panel**
2. Click **Stop** for Apache
3. Click **Stop** for MySQL
4. Close XAMPP Control Panel

---

## Step 3: Uninstall XAMPP

### Option A: Keep Data (Recommended)
- **DO NOT use Windows uninstaller**
- Simply rename the folder: `C:\xampp` → `C:\xampp_old`
- This keeps everything safe if you need to recover

### Option B: Full Uninstall
1. Go to **Windows Settings** → **Apps** → **Apps & Features**
2. Search for "XAMPP"
3. Click **Uninstall**
4. Follow the uninstall wizard
5. Delete remaining folder: `C:\xampp` (if it exists)

---

## Step 4: Download and Install Fresh XAMPP

1. Go to: https://www.apachefriends.org/download.html
2. Download **XAMPP for Windows** (same version if possible)
3. Run the installer
4. Install to: `C:\xampp` (default location)
5. **During installation:**
   - Install Apache ✓
   - Install MySQL ✓
   - Install PHP ✓
   - Install phpMyAdmin ✓

---

## Step 5: Restore Your Data

### 1. Restore MySQL Databases

**Option A: Restore from SQL file (phpMyAdmin)**
1. Start Apache and MySQL in XAMPP
2. Open `http://localhost/phpmyadmin/`
3. Click "Import" tab
4. Choose your backup file: `C:\xampp_backup\databases.sql` or `all_databases.sql`
5. Click "Go"

**Option B: Restore from data directory (FASTER)**
1. Stop MySQL in XAMPP Control Panel
2. Go to `C:\xampp\mysql\data`
3. **DELETE only the default databases** (information_schema, mysql, performance_schema folders - DO NOT DELETE YOUR DATABASES)
4. Copy your databases from `C:\xampp_backup\mysql_data_backup` 
5. Paste them into `C:\xampp\mysql\data`
6. Start MySQL

### 2. Restore Your Projects
1. Copy your projects from `C:\xampp_backup\htdocs_backup\ClearPay`
2. Paste to: `C:\xampp\htdocs\ClearPay`

### 3. Restore phpMyAdmin Config (Optional)
1. Copy: `C:\xampp_backup\config.inc.php`
2. Replace: `C:\xampp\phpMyAdmin\config.inc.php`
3. Edit it if needed (check user/password settings)

---

## Step 6: Test Everything

1. **Start Apache and MySQL** in XAMPP Control Panel
2. **Test phpMyAdmin:** `http://localhost/phpmyadmin/`
3. **Test your project:** `http://localhost/ClearPay/public/`
4. **Verify your databases** are showing in phpMyAdmin

---

## Quick Backup Script (Run in PowerShell as Administrator)

```powershell
# Create backup directory
$backupPath = "C:\xampp_backup"
New-Item -ItemType Directory -Path $backupPath -Force

# Backup MySQL databases
& "C:\xampp\mysql\bin\mysqldump.exe" --user=root --all-databases > "$backupPath\all_databases.sql"

# Backup htdocs
Copy-Item -Path "C:\xampp\htdocs" -Destination "$backupPath\htdocs_backup" -Recurse

# Backup MySQL data directory
Copy-Item -Path "C:\xampp\mysql\data" -Destination "$backupPath\mysql_data_backup" -Recurse

# Backup phpMyAdmin config
Copy-Item -Path "C:\xampp\phpMyAdmin\config.inc.php" -Destination "$backupPath\config.inc.php"
```

---

## Important Notes:

⚠️ **DO NOT DELETE** `C:\xampp_backup` until you've verified everything works!

⚠️ **Keep your old XAMPP folder** (`C:\xampp_old`) until restoration is complete!

⚠️ **Test everything** before deleting backups!

---

## If Something Goes Wrong:

1. Stop XAMPP services
2. Delete `C:\xampp`
3. Rename `C:\xampp_old` back to `C:\xampp`
4. Everything is back to original state!

