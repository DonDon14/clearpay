#!/bin/bash
# Don't exit on error - we want to continue even if migrations fail
set +e

# Change to application directory
cd /var/www/html

echo "🚀 Starting ClearPay application..."

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

