#!/bin/sh
set -eu

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

export APP_URL="${RENDER_EXTERNAL_URL:-${APP_URL:-http://localhost}}"

php artisan migrate --force
php artisan sus:seed-if-empty
php artisan config:cache
php artisan view:cache

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
