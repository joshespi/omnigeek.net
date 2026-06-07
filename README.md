# OmniGeek.net

Invite-only-to-post mini-blog. Public to read; posting + accounts are invite-only.

## Stack

Laravel · PHP · Livewire · MariaDB · nginx · Vite · Docker Compose. App code in `src/`; Docker config at repo root.

## First-time setup

```bash
cp .env.example .env            # set APP_URL, DB creds, INITIAL_USER_*
cp src/.env.example src/.env
docker compose build
docker compose run --rm app php artisan key:generate
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Runs at <http://localhost:8084> (change the `8084:80` mapping if it clashes; match `APP_URL`). Admin login is `INITIAL_USER_EMAIL` / `INITIAL_USER_PASSWORD` from the root `.env`. Log in at `/login`.

**Env:** root `.env` is per-deployment (injected into containers, wins on conflict); `src/.env` holds app defaults. Set `APP_URL` to the host clients actually reach (LAN IP/domain) — `localhost` breaks media URLs from other machines.

## Features

- **Feed** — public read, auth-gated posting. Markdown body, optional title/image/video (≤50 MB)/YouTube. Images compressed on upload. Scheduled/backdated posts via post date field.
- **Geeks** (`/geeks/{user}`) — per-user profile: avatar, bio, posts.
- **Categories** — admin-curated; chips link to `/categories/{slug}`.
- **Tags** — freeform, lowercased. `/tags` filters by selected tags (shareable URL).
- **Search** — `/search?q=` across title, body, author.
- **Admin** — `/admin`: post + category management, user list, activity log, site media. Gate: `is_admin = true`.
- **Email subscribe** (`/subscribe`) — double opt-in, no login. Filter by category/geek/tag. Delivery: `instant` or `digest` (daily/weekly/monthly).
- **Dark mode** — nav toggle, persisted.

## Inviting people

```bash
docker compose exec app php artisan invite:make [--email=friend@example.com] [--days=7]
```

`--email` locks to one address; `--days` sets expiry (`0` = never, default `14`). Prints a single-use `/register?invite=<token>`; invitees auto-verified.

## Seeding

`migrate --seed` runs: categories → admin user → demo content. All idempotent. Individual seeders:

```bash
docker compose exec app php artisan db:seed --class=InitialUserSeeder
docker compose exec app php artisan db:seed --class=DemoPostsSeeder
```

Demo accounts use `@omnigeek.test` / `password`.

## Local dev vs. production

The `Dockerfile` has two targets: **prod** (`--no-dev`, what deploys) and **dev** (adds the test toolchain so `php artisan test` runs in-container). `docker-compose.override.yml` (auto-merged locally) points `app` at the `dev` target and bind-mounts `src/resources` + `src/routes` for live view/route edits. Prod has no override, so it runs the lean image.

Rebuild (`docker compose build app`) on Dockerfile/dependency changes or PHP edits outside `resources`/`routes`.

No `npm` in containers — build assets via a throwaway node container:

```bash
docker run --rm -v "$PWD/src":/app -w /app node:24-alpine sh -c "npm run build"   # or: npm run dev
```

## Day-to-day

```bash
docker compose up -d
docker compose exec app php artisan test
docker compose exec app php artisan queue:work     # subscription emails
```

Tests use in-memory sqlite; overrides in `src/.env.testing`.