#!/bin/sh
# Run on the server. Pulls the repo, dumps the database BEFORE migrating, rebuilds the images
# (code, built assets and .env are baked into them), recreates the containers, then migrates
# and warms the Laravel caches. The queue worker and scheduler are recreated with the new image.
# Usage: scripts/deploy.sh [--no-pull] [--no-backup]
set -eu

cd "$(dirname "$0")/.."

pull=1
backup=1
for arg in "$@"; do
    case "$arg" in
        --no-pull) pull=0 ;;
        --no-backup) backup=0 ;;
        *) echo "Usage: $0 [--no-pull] [--no-backup]" >&2; exit 2 ;;
    esac
done

if [ ! -f .env ]; then
    echo "No .env found in $(pwd)." >&2
    exit 1
fi

if [ "$pull" = 1 ]; then
    echo "==> git pull"
    git pull --ff-only
fi

# Safety net before any migration runs. Skipped on a first deploy when the db isn't up yet.
if [ "$backup" = 1 ] && docker compose ps --status running --services | grep -qx db; then
    echo "==> pre-deploy database dump"
    mkdir -p backups
    out="backups/pre-deploy-$(date -u +%Y%m%dT%H%M%SZ).sql.gz"
    docker compose exec -T db sh -c 'mariadb-dump -uroot -p"$MARIADB_ROOT_PASSWORD" --single-transaction --routines "$MARIADB_DATABASE"' | gzip > "$out"
    test -s "$out" || { echo "dump is empty; aborting" >&2; rm -f "$out"; exit 1; }
    echo "    $out"
    # keep the 10 most recent pre-deploy dumps
    ls -1t backups/pre-deploy-*.sql.gz | tail -n +11 | xargs -r rm -f --
fi

echo "==> docker compose up -d --build"
docker compose up -d --build

echo "==> waiting for app"
tries=0
until docker compose exec -T app php -v >/dev/null 2>&1; do
    tries=$((tries + 1))
    if [ "$tries" -gt 30 ]; then
        echo "app did not come up; check: docker compose logs app" >&2
        exit 1
    fi
    sleep 2
done

app() { docker compose exec -T app "$@"; }

echo "==> migrate"
app php artisan migrate --force

echo "==> optimize"
app php artisan optimize:clear
app php artisan optimize

docker image prune -f >/dev/null

echo "Deployed $(git rev-parse --short HEAD)."
