#!/usr/bin/env sh
set -eu

echo "=== TIMAH ACADEMY Railway start ==="

# Normalisation robuste des variables Railway.
# Si les références DB_* ne sont pas résolues par l'interface Railway mobile,
# on reprend les variables natives MYSQL* ou le DNS privé Railway du service MySQL.
export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export DB_HOST="${DB_HOST:-${MYSQLHOST:-mysql.railway.internal}}"
export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
export DB_DATABASE="${DB_DATABASE:-${MYSQLDATABASE:-railway}}"
export DB_USERNAME="${DB_USERNAME:-${MYSQLUSER:-root}}"
export DB_PASSWORD="${DB_PASSWORD:-${MYSQLPASSWORD:-}}"
export CACHE_DRIVER="${CACHE_DRIVER:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

echo "DB_CONNECTION=${DB_CONNECTION}"
echo "DB_HOST=${DB_HOST}"
echo "DB_PORT=${DB_PORT}"
echo "DB_DATABASE=${DB_DATABASE}"
echo "DB_USERNAME=${DB_USERNAME}"
echo "MYSQLHOST=${MYSQLHOST:-not-set}"
echo "MYSQLPORT=${MYSQLPORT:-not-set}"
echo "MYSQLDATABASE=${MYSQLDATABASE:-not-set}"

echo "Clearing Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Attente active de MySQL : évite que Laravel tente les migrations avant que la DB soit prête.
echo "Waiting for MySQL connection..."
ATTEMPT=1
until php -r '
$host=getenv("DB_HOST");
$port=getenv("DB_PORT") ?: "3306";
$db=getenv("DB_DATABASE");
$user=getenv("DB_USERNAME");
$pass=getenv("DB_PASSWORD");
try {
    new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "MySQL connection OK\n";
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "MySQL not ready: ".$e->getMessage()."\n");
    exit(1);
}
'; do
    if [ "$ATTEMPT" -ge 30 ]; then
        echo "MySQL connection failed after ${ATTEMPT} attempts. Starting app without migrations to keep service alive."
        break
    fi
    echo "Retry ${ATTEMPT}/30 in 3s..."
    ATTEMPT=$((ATTEMPT + 1))
    sleep 3
done

if [ "$ATTEMPT" -lt 30 ]; then
    echo "Running database migrations..."
    php artisan migrate --force -vvv

    echo "Running database seeders..."
    php artisan db:seed --force -vvv || true
fi

echo "Starting Laravel server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
