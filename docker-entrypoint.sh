#!/usr/bin/env bash
set -e

# Run database migrations and seed default data
echo "Running database migrations..."
php artisan migrate --force

echo "Seeding initial data if missing..."
php artisan db:seed --force || true

# Start Apache in foreground
exec apache2-foreground
