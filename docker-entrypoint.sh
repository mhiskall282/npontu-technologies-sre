#!/usr/bin/env bash
set -e

echo "=== Initializing Opsora SRE Platform ==="

# Normalize APP_KEY for Laravel AES-256-CBC cipher compatibility.
# Render generateValue creates arbitrary-length strings without base64 prefix.
NORMALIZED_KEY=$(php -r '
    $key = getenv("APP_KEY") ?: "";
    if (str_starts_with($key, "base64:")) {
        $decoded = base64_decode(substr($key, 7), true);
        if ($decoded !== false && strlen($decoded) === 32) {
            echo $key;
            exit(0);
        }
    }
    if (strlen($key) === 32) {
        echo "base64:" . base64_encode($key);
        exit(0);
    }
    if (!empty($key)) {
        echo "base64:" . base64_encode(hash("sha256", $key, true));
        exit(0);
    }
    echo "base64:" . base64_encode(random_bytes(32));
')

export APP_KEY="$NORMALIZED_KEY"
echo "APP_KEY normalized successfully for AES-256-CBC."

# Persist normalized key to .env file
touch /var/www/html/.env
if grep -q "^APP_KEY=" /var/www/html/.env; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" /var/www/html/.env
else
    echo "APP_KEY=${APP_KEY}" >> /var/www/html/.env
fi
chmod 644 /var/www/html/.env || true
chown www-data:www-data /var/www/html/.env || true

# Export to Apache envvars so web workers inherit it
if [ -d /etc/apache2 ]; then
    echo "export APP_KEY=\"${APP_KEY}\"" >> /etc/apache2/envvars 2>/dev/null || true
fi

# Clear old build-time caches so runtime env vars (DATABASE_URL, APP_KEY) load dynamically
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Re-cache configuration for performance with normalized APP_KEY
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

# Start Apache or execute custom command (worker/scheduler)
if [ "$#" -gt 0 ]; then
    echo "Executing custom container command: $@"
    exec "$@"
else
    echo "Starting Apache web server on port 80..."
    exec apache2-foreground
fi



