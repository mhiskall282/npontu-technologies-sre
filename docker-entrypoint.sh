#!/usr/bin/env bash
set -e

# Generate APP_KEY if not set in environment
if [ -z "$APP_KEY" ]; then
    echo "Generating temporary APP_KEY..."
    php artisan key:generate --force
fi

# Ensure storage directories have proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run database migrations and seed default data
echo "Running database migrations..."
php artisan migrate --force

echo "Seeding initial data if missing..."
php artisan db:seed --force || true

# Start Apache in foreground
exec apache2-foreground

