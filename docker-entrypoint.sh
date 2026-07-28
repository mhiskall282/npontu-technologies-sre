#!/usr/bin/env bash
set -e

# Generate APP_KEY if not set in environment
if [ -z "$APP_KEY" ]; then
    echo "Generating temporary APP_KEY..."
    php artisan key:generate --force
fi

# Clear old build-time caches so runtime env vars (DATABASE_URL, APP_KEY) load dynamically
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Re-cache configuration for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

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


