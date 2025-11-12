#!/bin/bash
# Don't exit on error - we want to continue even if migrations fail
set +e

# Change to application directory
cd /var/www/html

echo "🚀 Starting ClearPay application..."

# Ensure .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    touch .env
    # Add minimal required config
    echo "CI_ENVIRONMENT = production" >> .env
    echo "app.appTimezone = Asia/Manila" >> .env
    # Base URL will be auto-detected by App.php, but we can set a default
    # App.php will override this with the actual URL from the request
fi

# Generate encryption key if not set in .env
if ! grep -q "^encryption.key" .env 2>/dev/null || grep -q "^encryption.key[[:space:]]*=[[:space:]]*$" .env 2>/dev/null; then
    echo "🔑 Generating encryption key..."
    # Use CodeIgniter's key:generate command to ensure correct format
    php spark key:generate --force 2>/dev/null || {
        # Fallback: generate manually if spark command fails
        ENCRYPTION_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
        if ! grep -q "^encryption.key" .env 2>/dev/null; then
            echo "encryption.key = $ENCRYPTION_KEY" >> .env
        else
            sed -i "s|^encryption.key.*|encryption.key = $ENCRYPTION_KEY|" .env
        fi
        echo "✅ Encryption key generated (manual)"
    }
    echo "✅ Encryption key configured"
fi

# Ensure writable directories exist and have correct permissions
echo "📁 Setting up writable directories..."
mkdir -p writable/session writable/logs writable/cache writable/uploads
chown -R www-data:www-data writable
chmod -R 775 writable
echo "✅ Writable directories configured"

# Wait a bit for database to be ready (simple delay)
echo "⏳ Waiting for database to be ready..."
sleep 5

# Run migrations
echo "🔄 Running migrations..."
php spark migrate || echo "⚠️  Migrations completed (or already up to date)"

# Run seeders (seeders are now idempotent - they check if data exists first)
echo "🌱 Running seeders..."
php spark db:seed DatabaseSeeder 2>&1 | grep -v "duplicate key\|already exists" || echo "✅ Seeders completed"

echo "✅ Setup complete! Starting Apache..."

# Start Apache
exec apache2-foreground

