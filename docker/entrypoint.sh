#!/bin/bash
set -e

# Setup Laravel
[ ! -d vendor ] && composer install --no-dev --optimize-autoloader
[ ! -f .env ] && cp .env.example .env
grep -q "APP_KEY=$" .env && php artisan key:generate
sed -i "s/DB_HOST=127.0.0.1/DB_HOST=database/" .env

# Start PHP-FPM in background
php-fpm &
PID=$!

# Wait for DB and run migrations
while ! pg_isready -h database -p 5432 -U laravel >/dev/null 2>&1; do sleep 1; done
php artisan migrate --force
php artisan config:cache

# Keep container running
wait $PID