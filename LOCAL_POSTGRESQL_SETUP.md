# Local PostgreSQL Setup Guide for ClearPay

This guide will help you set up ClearPay to run locally with PostgreSQL for faster testing.

## Prerequisites

- XAMPP (for Apache/PHP)
- PostgreSQL installed on your Windows machine
- Composer installed
- Git (if cloning from repository)

---

## Step 1: Install PostgreSQL

### Download and Install PostgreSQL

1. **Download PostgreSQL:**
   - Go to: https://www.postgresql.org/download/windows/
   - Download the Windows installer (recommended: PostgreSQL 14 or 15)
   - Run the installer

2. **During Installation:**
   - **Installation Directory:** Keep default (usually `C:\Program Files\PostgreSQL\15`)
   - **Data Directory:** Keep default (usually `C:\Program Files\PostgreSQL\15\data`)
   - **Port:** Keep default `5432` (or note if different)
   - **Advanced Options:** Leave defaults
   - **Password:** **IMPORTANT** - Set a password for the `postgres` superuser
     - Example: `postgres123` (remember this!)
   - **Locale:** Leave default or select `English, United States`

3. **Stack Builder (Optional):**
   - You can skip Stack Builder for now
   - Click "Finish" when installation completes

### Verify Installation

1. **Check PostgreSQL Service:**
   - Press `Win + R`, type `services.msc`, press Enter
   - Look for "postgresql-x64-15" (or your version)
   - Status should be "Running"
   - If not running, right-click → Start

2. **Test Connection:**
   - Open Command Prompt
   - Navigate to PostgreSQL bin directory:
     ```cmd
     cd "C:\Program Files\PostgreSQL\15\bin"
     ```
   - Test connection:
     ```cmd
     psql -U postgres
     ```
   - Enter your password when prompted
   - You should see: `postgres=#`
   - Type `\q` and press Enter to exit

---

## Step 2: Create Database and User

### Option A: Using pgAdmin (GUI - Recommended for Beginners)

1. **Open pgAdmin:**
   - Search for "pgAdmin 4" in Start Menu
   - Open pgAdmin 4

2. **Connect to Server:**
   - When pgAdmin opens, it will ask for the master password (set during installation)
   - Enter your password
   - In the left panel, expand "Servers" → "PostgreSQL 15" (or your version)

3. **Create Database:**
   - Right-click on "Databases" → "Create" → "Database..."
   - **Database name:** `clearpaydb`
   - **Owner:** `postgres`
   - Click "Save"

4. **Create User (Optional but Recommended):**
   - Right-click on "Login/Group Roles" → "Create" → "Login/Group Role..."
   - **General tab:**
     - **Name:** `clearpay_user`
   - **Definition tab:**
     - **Password:** `clearpay123` (or your preferred password)
   - **Privileges tab:**
     - Check "Can login?"
   - Click "Save"

5. **Grant Permissions:**
   - Right-click on `clearpaydb` database → "Properties"
   - Go to "Security" tab
   - Click "Add" → Select `clearpay_user`
   - Grant "ALL" privileges
   - Click "Save"

### Option B: Using Command Line (psql)

1. **Open Command Prompt as Administrator**

2. **Connect to PostgreSQL:**
   ```cmd
   cd "C:\Program Files\PostgreSQL\15\bin"
   psql -U postgres
   ```
   Enter your password when prompted.

3. **Create Database:**
   ```sql
   CREATE DATABASE clearpaydb;
   ```

4. **Create User (Optional):**
   ```sql
   CREATE USER clearpay_user WITH PASSWORD 'clearpay123';
   GRANT ALL PRIVILEGES ON DATABASE clearpaydb TO clearpay_user;
   ```

5. **Exit psql:**
   ```sql
   \q
   ```

---

## Step 3: Install PHP PostgreSQL Extension

1. **Check if pgsql extension is installed:**
   - Open Command Prompt
   ```cmd
   php -m | findstr pgsql
   ```
   - If you see `pgsql` and `pdo_pgsql`, you're good!
   - If not, continue below

2. **Enable PostgreSQL Extension in PHP:**
   - Open `C:\xampp\php\php.ini` in a text editor
   - Search for `;extension=pgsql`
   - Remove the semicolon (`;`) to uncomment:
     ```ini
     extension=pgsql
     extension=pdo_pgsql
     ```
   - Save the file
   - Restart Apache in XAMPP Control Panel

3. **Verify Extension:**
   ```cmd
   php -m | findstr pgsql
   ```
   Should show: `pgsql` and `pdo_pgsql`

---

## Step 4: Configure ClearPay for Local PostgreSQL

### Create/Update .env File

1. **Navigate to project root:**
   ```cmd
   cd C:\xampp\htdocs\ClearPay
   ```

2. **Create .env file** (if it doesn't exist):
   ```cmd
   copy .env.example .env
   ```
   Or create a new file named `.env`

3. **Edit .env file** with these settings:
   ```env
   # Environment
   CI_ENVIRONMENT = development

   # Application
   app.baseURL = 'http://localhost/ClearPay/public/'
   app.appTimezone = 'Asia/Manila'

   # Security
   encryption.key = base64:YOUR_ENCRYPTION_KEY_HERE

   # Database - PostgreSQL Local
   database.default.hostname = localhost
   database.default.database = clearpaydb
   database.default.username = postgres
   database.default.password = postgres123
   database.default.DBDriver = Postgre
   database.default.port = 5432
   database.default.DBDebug = true
   ```

   **Important:** Replace:
   - `postgres123` with your actual PostgreSQL password
   - `YOUR_ENCRYPTION_KEY_HERE` with a generated key (see Step 5)

---

## Step 5: Generate Encryption Key

1. **Open Command Prompt in project directory:**
   ```cmd
   cd C:\xampp\htdocs\ClearPay
   ```

2. **Generate encryption key:**
   ```cmd
   php spark key:generate
   ```

3. **Copy the generated key** and update your `.env` file:
   ```env
   encryption.key = base64:PASTE_GENERATED_KEY_HERE
   ```

---

## Step 6: Install Dependencies

1. **Install Composer dependencies:**
   ```cmd
   composer install
   ```

---

## Step 7: Run Database Migrations

1. **Run migrations to create tables:**
   ```cmd
   php spark migrate
   ```

   This will create all the necessary tables in your PostgreSQL database.

2. **Verify tables were created:**
   - Open pgAdmin
   - Connect to your database
   - Expand `clearpaydb` → `Schemas` → `public` → `Tables`
   - You should see all the tables (users, payers, payments, etc.)

---

## Step 8: Seed Database (Populate Initial Data)

1. **Run seeders to populate data:**
   ```cmd
   php spark db:seed DatabaseSeeder
   ```

   This will:
   - Create admin user
   - Create payment methods (GCash, PayMaya, etc.)
   - Create sample contributions
   - Create other initial data

2. **Verify data was seeded:**
   - In pgAdmin, right-click on `users` table → "View/Edit Data" → "All Rows"
   - You should see at least one admin user
   - Check `payment_methods` table for payment methods

---

## Step 9: Test the Application

1. **Start Apache in XAMPP Control Panel**

2. **Open browser:**
   ```
   http://localhost/ClearPay/public/
   ```

3. **Login with default admin credentials:**
   - **Username:** `admin`
   - **Password:** `admin123`

4. **Test features:**
   - Create a payment
   - Upload profile picture
   - Test email functionality (if configured)

---

## Troubleshooting

### Issue: "Could not connect to database"

**Solutions:**
1. Check PostgreSQL service is running:
   - `services.msc` → Look for PostgreSQL service → Start if stopped

2. Verify connection details in `.env`:
   - Host: `localhost`
   - Port: `5432`
   - Database: `clearpaydb`
   - Username: `postgres` (or your user)
   - Password: Your PostgreSQL password

3. Test connection manually:
   ```cmd
   cd "C:\Program Files\PostgreSQL\15\bin"
   psql -U postgres -d clearpaydb
   ```

### Issue: "pgsql extension not found"

**Solutions:**
1. Enable extension in `php.ini`:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```

2. Restart Apache in XAMPP

3. Verify:
   ```cmd
   php -m | findstr pgsql
   ```

### Issue: "Migration failed"

**Solutions:**
1. Ensure database exists:
   ```sql
   CREATE DATABASE clearpaydb;
   ```

2. Check user has permissions:
   ```sql
   GRANT ALL PRIVILEGES ON DATABASE clearpaydb TO postgres;
   ```

3. Try running migrations again:
   ```cmd
   php spark migrate
   ```

### Issue: "Table already exists"

**Solutions:**
1. If you want to start fresh:
   ```cmd
   php spark migrate:rollback
   php spark migrate
   ```

2. Or manually drop tables in pgAdmin

---

## Quick Setup Script (Windows)

Create a file `setup-local-postgres.bat` in your project root:

```batch
@echo off
echo ========================================
echo ClearPay Local PostgreSQL Setup
echo ========================================
echo.

echo Step 1: Installing PHP dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Composer install failed!
    pause
    exit /b 1
)
echo.

echo Step 2: Generating encryption key...
php spark key:generate
echo.

echo Step 3: Running database migrations...
php spark migrate
if %errorlevel% neq 0 (
    echo ERROR: Migrations failed! Check PostgreSQL connection.
    pause
    exit /b 1
)
echo.

echo Step 4: Seeding database...
php spark db:seed DatabaseSeeder
if %errorlevel% neq 0 (
    echo ERROR: Seeding failed!
    pause
    exit /b 1
)
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Start Apache in XAMPP
echo 2. Open http://localhost/ClearPay/public/
echo 3. Login with admin/admin123
echo.
pause
```

Run it:
```cmd
setup-local-postgres.bat
```

---

## Switching Between MySQL and PostgreSQL

### To Use PostgreSQL:
```env
database.default.DBDriver = Postgre
database.default.port = 5432
```

### To Use MySQL:
```env
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Then restart Apache and run migrations again.

---

## Useful PostgreSQL Commands

### Connect to Database:
```cmd
cd "C:\Program Files\PostgreSQL\15\bin"
psql -U postgres -d clearpaydb
```

### List All Tables:
```sql
\dt
```

### View Table Structure:
```sql
\d table_name
```

### Exit psql:
```sql
\q
```

---

## Next Steps

After setup is complete:
1. ✅ Test email functionality locally
2. ✅ Test payment creation
3. ✅ Test file uploads
4. ✅ Debug any issues locally
5. ✅ Deploy to Render when ready

---

## Need Help?

If you encounter issues:
1. Check `writable/logs/` for error logs
2. Verify PostgreSQL service is running
3. Check `.env` file configuration
4. Verify PHP extensions are enabled
5. Check database connection manually using psql

