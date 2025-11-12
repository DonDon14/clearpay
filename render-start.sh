#!/bin/bash
# Render.com Start Script for ClearPay
# This script runs when the service starts

set -e  # Exit on error

echo "🚀 Starting ClearPay application..."

# Ensure writable directories exist and have correct permissions
mkdir -p writable/cache
mkdir -p writable/logs
mkdir -p writable/session
mkdir -p writable/uploads
mkdir -p writable/debugbar
mkdir -p writable/backups

# Set permissions
chmod -R 775 writable/ || true

# Parse DATABASE_URL if provided (Render provides this for PostgreSQL)
# For MySQL, we'll use individual environment variables
if [ -n "$DATABASE_URL" ]; then
    echo "📊 Database URL detected, parsing connection string..."
    # This is mainly for PostgreSQL
    # For MySQL, use individual env vars instead
fi

# Start PHP built-in server
# Render provides $PORT environment variable
echo "🌐 Starting PHP server on port ${PORT:-10000}..."

# Use PHP built-in server with router script
php -S 0.0.0.0:${PORT:-10000} -t public public/index.php

