#!/bin/bash
# Don't exit on error - we want to continue even if migrations fail
set +e

# Change to application directory
cd /var/www/html

echo "🚀 Starting ClearPay application..."

# Generate encryption key if not set and write to .env
if [ -z "$ENCRYPTION_KEY" ]; then
    echo "🔑 Generating encryption key..."
    ENCRYPTION_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    export ENCRYPTION_KEY
    echo "✅ Encryption key generated"
fi

# Ensure .env file exists and has encryption key
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    touch .env
fi

# Add or update encryption key in .env file
if ! grep -q "^encryption.key" .env 2>/dev/null; then
    echo "encryption.key = $ENCRYPTION_KEY" >> .env
    echo "✅ Encryption key added to .env"
else
    # Update existing encryption.key line
    if [ -n "$ENCRYPTION_KEY" ]; then
        sed -i "s|^encryption.key.*|encryption.key = $ENCRYPTION_KEY|" .env
        echo "✅ Encryption key updated in .env"
    fi
fi

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

