#!/bin/sh
set -e
cd /var/www/html

# Auto-install composer dependencies if missing
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
    chown -R www-data:www-data vendor
fi

# Laravel needs to write to storage and bootstrap/cache (views, logs, sessions, cache)
chown -R www-data:www-data storage bootstrap/cache

# Company logos (uploaded in app, committed via volume or later)
mkdir -p public/company-logos
chown -R www-data:www-data public/company-logos

# Blog article images
mkdir -p public/blog-images
chown -R www-data:www-data public/blog-images

# Generate APP_KEY if .env exists but key is empty (required for encryption)
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

# If arguments passed, run them instead of php-fpm
# This allows: docker compose run --rm app composer install
#              docker compose run --rm app php artisan migrate
if [ $# -gt 0 ]; then
    exec "$@"
fi

# Default: run php-fpm as root; pool config runs workers as www-data
exec php-fpm
