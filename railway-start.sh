#!/usr/bin/env sh
set -eu

echo "=== TIMAH ACADEMY Railway start ==="
echo "DB_CONNECTION=${DB_CONNECTION:-not-set}"
echo "DB_HOST=${DB_HOST:-not-set}"
echo "DB_PORT=${DB_PORT:-not-set}"
echo "DB_DATABASE=${DB_DATABASE:-not-set}"
echo "MYSQLHOST=${MYSQLHOST:-not-set}"
echo "MYSQLPORT=${MYSQLPORT:-not-set}"
echo "MYSQLDATABASE=${MYSQLDATABASE:-not-set}"

echo "Clearing Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "Waiting briefly before database migration..."
sleep 5

echo "Running database migrations..."
php artisan migrate --force -vvv

echo "Running database seeders..."
php artisan db:seed --force -vvv || true

echo "Starting Laravel server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
