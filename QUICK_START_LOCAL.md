# Quick Start - Local PostgreSQL Setup

## Prerequisites Checklist

- [ ] PostgreSQL installed
- [ ] PostgreSQL service running
- [ ] XAMPP installed (for Apache/PHP)
- [ ] Composer installed
- [ ] PHP pgsql extension enabled

---

## 5-Minute Setup

### 1. Install PostgreSQL (if not installed)
- Download from: https://www.postgresql.org/download/windows/
- During installation:
  - Set password for `postgres` user (remember this!)
  - Keep port as `5432`
  - Complete installation

### 2. Create Database
Open pgAdmin or Command Prompt:

**Using pgAdmin (GUI):**
1. Open pgAdmin 4
2. Connect to PostgreSQL server
3. Right-click "Databases" → Create → Database
4. Name: `clearpaydb`
5. Click Save

**Using Command Line:**
```cmd
cd "C:\Program Files\PostgreSQL\15\bin"
psql -U postgres
```
Then:
```sql
CREATE DATABASE clearpaydb;
\q
```

### 3. Enable PHP PostgreSQL Extension
1. Open `C:\xampp\php\php.ini`
2. Find and uncomment:
   ```ini
   extension=pgsql
   extension=pdo_pgsql
   ```
3. Save and restart Apache

### 4. Configure .env File
1. Copy `.env.example.postgresql` to `.env`:
   ```cmd
   copy .env.example.postgresql .env
   ```

2. Edit `.env` and update:
   ```env
   database.default.password = YOUR_POSTGRES_PASSWORD
   ```

### 5. Run Setup Script
```cmd
setup-local-postgres.bat
```

Or manually:
```cmd
composer install
php spark key:generate
php spark migrate
php spark db:seed DatabaseSeeder
```

### 6. Start Application
1. Start Apache in XAMPP
2. Open: http://localhost/ClearPay/public/
3. Login: `admin` / `admin123`

---

## Verify Setup

### Check Database Connection
```cmd
php -r "require 'vendor/autoload.php'; $db = \Config\Database::connect(); echo 'Connected!' . PHP_EOL;"
```

### Check Tables
In pgAdmin:
- Expand `clearpaydb` → `Schemas` → `public` → `Tables`
- Should see: users, payers, payments, etc.

### Check Data
```cmd
php -r "require 'vendor/autoload.php'; $db = \Config\Database::connect(); echo 'Users: ' . $db->table('users')->countAllResults() . PHP_EOL; echo 'Payment Methods: ' . $db->table('payment_methods')->countAllResults() . PHP_EOL;"
```

---

## Common Issues

### "Could not connect to database"
- Check PostgreSQL service is running (`services.msc`)
- Verify password in `.env` matches PostgreSQL password
- Test connection: `psql -U postgres -d clearpaydb`

### "pgsql extension not found"
- Enable in `php.ini`: `extension=pgsql` and `extension=pdo_pgsql`
- Restart Apache
- Verify: `php -m | findstr pgsql`

### "Table already exists"
- Drop database and recreate:
  ```sql
  DROP DATABASE clearpaydb;
  CREATE DATABASE clearpaydb;
  ```
- Run migrations again: `php spark migrate`

---

## Next Steps

1. ✅ Test email functionality
2. ✅ Test payment creation
3. ✅ Test file uploads
4. ✅ Debug issues locally
5. ✅ Deploy when ready

---

## Need Detailed Instructions?

See `LOCAL_POSTGRESQL_SETUP.md` for complete step-by-step guide.

