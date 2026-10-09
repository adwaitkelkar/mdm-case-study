#!/usr/bin/env bash
set -e

cd /var/www/html

# When ./backend is bind-mounted from the host for local development, an
# empty named volume for vendor/ can shadow what was built into the image -
# make sure dependencies are present either way before booting.
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] Installing composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
    echo "[entrypoint] Creating .env from .env.example..."
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64' .env; then
    echo "[entrypoint] Generating APP_KEY..."
    php artisan key:generate --force
fi

DB_HOST_VALUE="${DB_HOST:-mysql}"
DB_PORT_VALUE="${DB_PORT:-3306}"
DB_USERNAME_VALUE="${DB_USERNAME:-workwize}"
DB_PASSWORD_VALUE="${DB_PASSWORD:-workwize_secret}"

echo "[entrypoint] Waiting for MySQL at ${DB_HOST_VALUE}:${DB_PORT_VALUE}..."
until php -r "new PDO('mysql:host=${DB_HOST_VALUE};port=${DB_PORT_VALUE}', '${DB_USERNAME_VALUE}', '${DB_PASSWORD_VALUE}');" >/dev/null 2>&1; do
    sleep 2
done
echo "[entrypoint] MySQL is up."

php artisan migrate --force

echo "[entrypoint] Starting: $*"
exec "$@"
