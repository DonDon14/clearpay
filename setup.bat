@echo off
echo ========================================
echo ClearPay Setup Script
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

echo Step 2: Running database migrations...
php spark migrate
if %errorlevel% neq 0 (
    echo ERROR: Migrations failed! Check database connection.
    pause
    exit /b 1
)
echo.

echo Step 3: Seeding database (CRITICAL - Creates payment methods)...
php spark db:seed DatabaseSeeder
if %errorlevel% neq 0 (
    echo ERROR: Seeding failed!
    pause
    exit /b 1
)
echo.

echo Step 4: Verifying payment methods were seeded...
php -r "require 'vendor/autoload.php'; $db = \Config\Database::connect(); $result = $db->query('SELECT COUNT(*) as count FROM payment_methods WHERE status = \"active\"')->getRow(); echo 'Active payment methods: ' . $result->count . PHP_EOL; if ($result->count < 4) { echo 'WARNING: Expected at least 4 payment methods!' . PHP_EOL; exit(1); }"
if %errorlevel% neq 0 (
    echo WARNING: Payment methods verification failed. Running PaymentMethodSeeder...
    php spark db:seed PaymentMethodSeeder
)
echo.

echo Step 5: Generating encryption key (if needed)...
php spark key:generate
echo.

echo Step 6: Clearing cache...
php spark cache:clear
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Please verify:
echo 1. Payment methods exist in database (check phpMyAdmin)
echo 2. Admin user exists (username: admin, password: admin123)
echo 3. Database tables are created
echo.
echo Next steps:
echo 1. Start Apache and MySQL in XAMPP
echo 2. Open http://localhost/ClearPay/public/
echo 3. Login with admin/admin123
echo 4. Try creating a payment to verify everything works
echo.
pause

