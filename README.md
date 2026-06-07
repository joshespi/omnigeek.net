# OmniGeek.net

Small invite-only-to-post mini-blog (OmniGeek). Public to read; posting + accounts are invite-only.

## Stack

Laravel · PHP · Livewire · MariaDB · nginx · Vite · Docker Compose.

App code in `src/`; Docker config (`Dockerfile`, `Dockerfile.nginx`, `docker-compose.yml`, `nginx.conf`) at repo root.

## First-time setup

```bash
cp .env.example .env            # deployment config (edit APP_URL + DB creds)
cp src/.env.example src/.env    # app defaults — usually untouched
docker compose build
docker compose run --rm -u 1000 app php artisan key:generate   # writes APP_KEY to root .env
docker compose up -d
docker compose exec -u 1000 app php artisan migrate --seed   # seeds categories, the admin user, and demo content
docker compose exec -u 1000 app php artisan storage:link
```

Runs at <http://localhost:8084> (change the `8084:80` mapping in `docker-compose.yml` if it clashes, and match `APP_URL`).

Set `INITIAL_USER_EMAIL` / `INITIAL_USER_PASSWORD` in the root `.env` **before** seeding — that's your admin login (created by `InitialUserSeeder`). Log in at `/login`.

### Env files

- **Root `.env`** — per-deployment source of truth (`APP_*`, DB creds, `INITIAL_USER_*`, `APP_VERSION`). docker-compose injects it into the `app` container, overriding `src/.env`.
- **`src/.env`** — app defaults (`APP_NAME`, drivers, DB host/port/name, `VITE_*`).

Rule: deployment-specific/secret → root; app default → `src/`. Root wins on conflict.

> `APP_URL` builds asset/media URLs — set it to the host clients actually reach (LAN IP/domain), not `localhost`, or images 404 from other machines.

> php-fpm runs as uid/gid `1000`, so run artisan with `-u 1000` to keep generated files host-editable.

## Features

- **Feed** — public read, auth-gated posting. Markdown body, optional title/image/video (≤50 MB)/YouTube. Images compressed on upload (1600 px max, JPEG q82). Scheduled/backdated posts via post date field.
- **Geeks** (`/geeks/{user}`) — per-user profile: avatar (512 px, initials fallback), bio, posts.
- **Categories** — admin-curated pills in the composer; chips link to `/categories/{slug}`.
- **Tags** — freeform; collapsed to lowercase slug. `/tags` filters by selected tags (shareable URL).
- **Search** — `/search?q=` across title, body, author.
- **Admin** — `/admin`: post + category management, user list, activity log, site media (logo, OG image, digest cadence). Gate: `is_admin = true`.
- **Email subscribe** (`/subscribe`) — double opt-in, no login. Filter by category/geek/tag. Delivery: `instant` (queued on publish) or `digest` (daily/weekly/monthly via `digest:send` + scheduler).
- **Dark mode** — nav toggle, `localStorage`, applied pre-paint.

## Inviting people

No open signup. Mint a link:

```bash
docker compose exec -u 1000 app php artisan invite:make [--email=friend@example.com] [--days=7]
```

`--email` locks it to one address; `--days` sets expiry (`0` = never, default `14`). Prints `/register?invite=<token>` — single-use, consumed on signup, invitees auto-verified.

## Seeding

`migrate --seed` runs: categories → admin user (`INITIAL_USER_*`) → demo content (geeks, posts, media). All idempotent. Individual seeders:

```bash
docker compose exec -u 1000 app php artisan db:seed --class=InitialUserSeeder
docker compose exec -u 1000 app php artisan db:seed --class=DemoPostsSeeder
```

Demo accounts use `@omnigeek.test` / `password`.

## Local dev vs. production

The `Dockerfile` has two targets. **prod** (`composer install --no-dev`, no test toolchain) is what deploys — base compose pins `target: prod` for `app`, `worker`, `scheduler`. **dev** is the same image plus the `require-dev` packages and a dev autoloader (so `Tests\` resolves and `php artisan test` runs in-container).

Locally, `docker-compose.override.yml` (auto-merged) flips `app` to `target: dev` and bind-mounts `./src/resources` + `./src/routes` so view/route edits are live without a rebuild. Prod has no override file, so every service runs the lean prod image.

Rebuild (`docker compose build app`) only on Dockerfile/dependency changes — adding a Composer package, or editing PHP outside `resources`/`routes`, needs a rebuild to land in the image.

PHP upload limits: `upload_max_filesize=64M`, `post_max_size=72M` (set in Dockerfile); nginx `client_max_body_size 72M` — change together if needed.

No `npm` in containers. Rebuild assets or watch with a throwaway node container:

```bash
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm run build"   # one-shot
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm run dev"     # watch
```

## Day-to-day

```bash
docker compose up -d                                       # start
docker compose exec -u 1000 app php artisan test           # tests
docker compose exec -u 1000 app php artisan queue:work     # process queued subscription emails
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm run dev"   # asset watch
```

Runs in the `dev`-target `app` container (prod image has no phpunit). Uses in-memory sqlite; overrides in `src/.env.testing`. `phpunit.xml` forces `APP_ENV=testing` so the container's OS env doesn't shadow it.

## Roadmap

Group chat to replace Discord via Laravel Reverb (WebSockets) — not started. Other ideas in `BACKLOG.md`.
