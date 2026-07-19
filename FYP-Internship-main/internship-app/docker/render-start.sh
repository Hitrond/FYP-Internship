#!/bin/sh
set -eu

export APP_URL="${RENDER_EXTERNAL_URL:-${APP_URL:-http://localhost}}"

if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show --no-ansi)"
fi

php artisan migrate --force
php artisan sus:seed-if-empty
php artisan db:seed --class=SusApprovalAssetsSeeder --force
php artisan config:cache
php artisan view:cache

exec php \
    -d display_errors=Off \
    -d upload_max_filesize=105M \
    -d post_max_size=650M \
    artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
