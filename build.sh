#!/usr/bin/env bash
# Exit on error
set -o errexit

echo "Building PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Building assets..."
npm install
npm run build

echo "Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
