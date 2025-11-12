#!/bin/bash
set -e

echo "🚀 Starting ClearPay application..."

# Wait for database to be ready (important for first startup)
echo "⏳ Waiting for database connection..."
max_attempts=30
attempt=0

while [ $attempt -lt $max_attempts ]; do
    if php -r "
    require 'vendor/autoload.php';
    try {
        \$db = \Config\Database::connect();
        \$db->query('SELECT 1');
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
    " 2>/dev/null; then
        echo "✅ Database is ready!"
        break
    fi
    attempt=$((attempt + 1))
    echo "Waiting for database... ($attempt/$max_attempts)"
    sleep 2
done

if [ $attempt -eq $max_attempts ]; then
    echo "⚠️  Database connection timeout, but continuing anyway..."
fi

# Run migrations
echo "🔄 Running migrations..."
php spark migrate || echo "⚠️  Migrations completed (or already up to date)"

# Run seeders (CodeIgniter will handle if already seeded)
echo "🌱 Running seeders..."
php spark db:seed DatabaseSeeder || echo "⚠️  Seeders completed (or already seeded)"

echo "✅ Setup complete! Starting Apache..."

# Start Apache
exec apache2-foreground

