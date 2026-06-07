# mini-feed

Small invite-only-to-post mini-blog (OmniGeek). Public to read; posting + accounts are invite-only. Laravel 13 + Livewire (Breeze), Dockerized.

## Stack

Laravel 13 · PHP 8.5 (php-fpm) · Livewire 3 + Breeze · MariaDB 11.4 · nginx · Vite · Docker Compose.

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

- **Feed** (`/`) — public read; shareable permalinks at `/post/{id}`. Composer (logged-in only): markdown body (bold/italic/links/lists/quotes/code; live preview toggle), optional title, one image/video ≤50 MB (jpg/png/gif/webp/mp4/webm/mov), and/or a YouTube URL. Images are compressed on upload (max 1600 px wide, JPEG q82 or PNG; EXIF-rotated). Videos stored as-is. Delete your own posts only (enforced server-side). Media under `storage/app/public/uploads` via the `public/storage` symlink.
- **Avatars** — set on the Profile page (≤5 MB, compressed to 512 px); initials circle as fallback.
- **Geeks** (`/geeks/{user}`) — public per-user profile: avatar, bio, their posts. Nav dropdown + post-card name links.
- **Categories** — fixed, admin-curated (seeded: Video Games, Reading, Movies/TV, Misc). Picked as pills in the composer; chips link to `/categories/{slug}`. Seeded by `CategorySeeder` (default `db:seed`).
- **Tags** — freeform hashtags, lowercased + slugged on save (`#Rust`/`rust`/`RUST` collapse to one), autocomplete hints in the composer. `#tag` chips link to `/tags/{slug}`. `/tags` filters the feed by ANY selected tag (selection in the URL, shareable).
- **Admin** — users with `is_admin = true` (primary user flagged by `DemoPostsSeeder`). `/admin` + `/admin/categories` (category CRUD) + `/admin/media` (logo, OG image, digest cadence), gated by `can:admin`. Flag others: `User::where('email', …)->update(['is_admin' => true])`. Logo and OG image are compressed on upload; URLs include a `?v={APP_VERSION}-{mtime}` cache-bust token.
- **Email subscribe** (`/subscribe`) — double opt-in, token-based confirm/unsubscribe (no login). Scope by any mix of categories/geeks/tags (JSON `filters`, match ANY; empty = everything). Two delivery modes per subscriber (`frequency` column): `instant` queues `NotifySubscribersOfNewPost` on each publish; `digest` is batched by the `digest:send` command. Digest cadence (daily/weekly/monthly) is a global admin setting at `/admin/media`, read by the scheduler from cache (`DigestCadence`); `digest:send` covers posts since each subscriber's `last_notified_at` (falling back to one cadence-window). The `channel` column is a seam for future push/Discord — only email wired. Needs a mailer, a queue worker, and (for digests) the `scheduler` service running `schedule:work`.
- **Dark mode** — nav toggle, saved in `localStorage`, applied pre-paint; Tailwind `darkMode: 'class'`.

## Inviting people

No open signup. Mint a link:

```bash
docker compose exec -u 1000 app php artisan invite:make [--email=friend@example.com] [--days=7]
```

`--email` locks it to one address; `--days` sets expiry (`0` = never, default `14`). Prints `/register?invite=<token>` — single-use, consumed on signup, invitees auto-verified.

## Seeding

`php artisan migrate --seed` (or `migrate:fresh --seed`) runs `DatabaseSeeder`: categories → the **admin user** (`InitialUserSeeder`, from `INITIAL_USER_*`) → demo content (`DemoPostsSeeder`: named geeks with bios/posts/tags + sample media on the admin user). All idempotent.

Run a piece on its own:

```bash
docker compose exec -u 1000 app php artisan db:seed --class=InitialUserSeeder   # just the admin login
docker compose exec -u 1000 app php artisan db:seed --class=DemoPostsSeeder     # geeks + sample posts
```

Geeks use `@omnigeek.test` / `password` — local demo accounts only.

## Local dev vs. production

The `Dockerfile` builds a lean **prod** image (bakes `src/`, `composer install --no-dev`, compiled assets) — that's what deploys.

Locally, `docker-compose.override.yml` (auto-merged) bind-mounts `./src` into **both** `app` and `nginx`, so edits/`git pull` are live and `composer test` uses the host's dev `vendor`. Both containers need the mount — PHP reads the Vite manifest, nginx serves the files; mount only one and the hashes drift → 404/no CSS. Prod has no override file, so it runs the baked image. Same commands everywhere.

Rebuild (`docker compose build`) only on Dockerfile/dependency changes. After a fresh clone: `docker compose exec -u 1000 app composer install` once.

Image compression uses [`intervention/image`](https://image.intervention.io/) v4 with the GD driver (GD is compiled into the image with JPEG/PNG/WebP support). The Dockerfile also sets PHP upload limits (`upload_max_filesize=64M`, `post_max_size=72M`, `memory_limit=256M`) and nginx `client_max_body_size 72M` — change both together if you need a different cap.

There's no `npm` in the containers — build assets via a throwaway node container (live immediately under the override). Rerun after adding Tailwind classes:

```bash
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm install && npm run build"
```

## Day-to-day

```bash
docker compose up -d                                       # start
docker compose exec -u 1000 app composer test              # tests
docker compose exec -u 1000 app php artisan queue:work     # process queued subscription emails
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm run dev"   # asset watch
```

Use `composer test`, not `php artisan test` — the script forces `APP_ENV=testing` (otherwise the container's OS `APP_ENV=local` shadows it and Livewire test macros don't register). Test overrides live in `src/.env.testing`.

## Roadmap

Group chat to replace Discord via Laravel Reverb (WebSockets) — not started. Other ideas in `BACKLOG.md`.
