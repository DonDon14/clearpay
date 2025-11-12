#!/bin/bash
# Render.com Build Script for ClearPay
# This script runs during the build phase

set -e  # Exit on error

echo "🚀 Starting ClearPay build process..."

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Create writable directories if they don't exist
echo "📁 Setting up writable directories..."
mkdir -p writable/cache
mkdir -p writable/logs
mkdir -p writable/session
mkdir -p writable/uploads
mkdir -p writable/debugbar
mkdir -p writable/backups

# Set permissions (Render handles this, but good to ensure)
chmod -R 775 writable/ || true

# Generate encryption key if not set
if [ -z "$ENCRYPTION_KEY" ]; then
    echo "🔑 Generating encryption key..."
    php spark key:generate --force || echo "⚠️  Key generation skipped (may already exist)"
fi

# Run database migrations (optional - can be done via Render shell)
# Uncomment if you want migrations to run automatically on deploy
# echo "🗄️  Running database migrations..."
# php spark migrate || echo "⚠️  Migrations skipped (database may not be ready)"

echo "✅ Build completed successfully!"

