#!/bin/sh
set -e

# A fresh storage volume mounts in root-owned; FPM runs as www-data and must be
# able to write uploads. Re-assert ownership on boot so it's always writable.
chown -R www-data:www-data storage/app/public

php artisan storage:link --force 2>/dev/null || true

exec php-fpm
