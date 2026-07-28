# ── Stage 1: Node build (Tailwind CSS assets) ─────────────────────────────────
FROM node:20-alpine AS assets

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ── Stage 2: PHP runtime (Apache) ─────────────────────────────────────────────
FROM php:8.2-apache

# System dependencies + PHP extensions required by Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite (needed for Laravel routing)
RUN a2enmod rewrite

# Configure Apache to serve from /var/www/html/public and set ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|<Directory /var/www/>|<Directory /var/www/html/public/>|g' /etc/apache2/apache2.conf \
    && echo '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>' \
       >> /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy full application code first
COPY . .

# Copy compiled assets from stage 1
COPY --from=assets /app/public/build ./public/build

# Install PHP dependencies
RUN composer update fakerphp/faker --no-interaction --no-scripts && composer install --no-dev --optimize-autoloader --no-scripts

# Laravel storage and cache directories
RUN mkdir -p storage/framework/{cache,sessions,views} \
             storage/logs \
             bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

# Startup script: migrate + seed (if first deploy) then start Apache
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
