#!/bin/sh
set -e

# A fresh storage volume mounts in root-owned; FPM runs as www-data and must be
# able to write logs/cache/uploads. Re-assert ownership only when the volume is
# not already www-data — skips a full recursive walk of the (growing) cache,
# log and session trees on every warm boot.
if [ "$(stat -c '%U' storage)" != "www-data" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
fi

php artisan storage:link --force 2>/dev/null || true

exec php-fpm
