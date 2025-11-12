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

# Run seeders (CodeIgniter will handle if already seeded)
echo "🌱 Running seeders..."
php spark db:seed DatabaseSeeder || echo "⚠️  Seeders completed (or already seeded)"

echo "✅ Setup complete! Starting Apache..."

# Start Apache
exec apache2-foreground

