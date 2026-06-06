# mini-feed

A small, invite-only-to-post mini-blog (OmniGeek). Post short text with an image, a video, or a YouTube embed. The feed is **public to read** — anyone can browse it and open individual posts via shareable links — but posting and accounts are invite-only. Laravel 13 + Livewire (Breeze), Dockerized.

Chat / Discord-replacement is **not** in here yet — that's a planned phase 2 (Laravel Reverb).

## Stack

- Laravel 13, PHP 8.5 (php-fpm)
- Livewire 3 + Breeze (Livewire/Volt auth scaffolding)
- MariaDB 11.4
- nginx, Vite for assets
- Docker Compose

The app code lives in `src/`. The Docker config (`Dockerfile`, `docker-compose.yml`, `nginx.conf`) lives at the repo root.

## First-time setup

From the repo root:

```bash
cp .env.example .env                       # deployment config — see below
cp src/.env.example src/.env               # app defaults — usually no edits needed
docker compose build
docker compose run --rm app composer install
docker compose run --rm -u 1000 app php artisan key:generate   # writes APP_KEY into the root .env
docker compose run --rm node sh -c "npm install && npm run build"
docker compose up -d
docker compose exec -u 1000 app php artisan migrate
docker compose exec -u 1000 app php artisan storage:link
```

### Two env files, on purpose

- **Root `.env`** is the per-deployment source of truth. It holds everything unique to where the app runs — `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_DEBUG`, the DB credentials, and the demo user. docker-compose injects it into the `app` container via `env_file`, where it **overrides** the matching values in `src/.env`. The `db` container reads its credentials from here too.
- **`src/.env`** is Laravel's own env file with app-level defaults — `APP_NAME`, locales, drivers, DB host/port/name, and the `VITE_*` keys the asset build reads directly. Leave it mostly alone.

The split means: deployment-specific or secret → root `.env`; app default → `src/.env`. Because the container's OS env (from root `.env`) wins, a value set in both resolves to the root one.

Root `.env` you actually edit:

```ini
APP_URL=http://localhost:8084   # must match how clients reach the app; use the LAN IP/domain, not localhost, if accessing from another machine
DB_USERNAME=laravel
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root
```

App runs at **http://localhost:8084**. (Host port is set in `docker-compose.yml` — change the `8084:80` mapping if it clashes, and update `APP_URL` to match.)

> **APP_URL gotcha:** `APP_URL` builds asset/media URLs (avatars, uploads). If you browse from a different machine, `localhost` points at the wrong host and images 404 — set it to the server's LAN IP or domain.

> **uid note:** the `app` container's php-fpm pool runs as uid/gid `1000` (set in the `Dockerfile`) so files it writes stay editable on the host. Run artisan with `-u 1000` (e.g. `docker compose exec -u 1000 app php artisan ...`) so generated files aren't owned by root.

## Inviting people (it's invite-only)

There is no open signup. Mint an invite link:

```bash
docker compose exec -u 1000 app php artisan invite:make
docker compose exec -u 1000 app php artisan invite:make --email=friend@example.com --days=7
```

Options:
- `--email=` — lock the invite to one email address (optional)
- `--days=` — days until expiry, `0` for no expiry (default `14`)

It prints a `/register?invite=<token>` link. The token is single-use and consumed on signup. Invited users are auto-verified (no mail server needed).

## Reading & posting

The feed lives at `/` and is **public** — logged-out visitors see every post and can open any single post at `/post/{id}` (a shareable permalink; the timestamp on each card links to it, and the post page has a "Copy link" button).

The composer only appears when logged in. It takes:
- short text (≤ 1000 chars)
- one image or video upload (≤ 50 MB; jpg/png/gif/webp/mp4/webm/mov)
- a pasted YouTube URL (watch / youtu.be / embed / shorts links all work)

Any combination is fine. You can delete your own posts (from the feed or the post page); you can't delete anyone else's. Guests can't post — the action is blocked server-side, not just hidden.

Uploaded media is stored on local disk under `storage/app/public/uploads` and served via the `public/storage` symlink.

## Avatars

Each user can set a profile avatar from the **Profile** page (≤ 5 MB image). It shows next to their posts; users without one get an initials circle. Replacing an avatar deletes the old file; there's a Remove button to clear it.

## Geeks (per-user profiles)

The **Geeks** dropdown in the nav lists everyone who has posted. Each links to a public profile at `/geeks/{user}` showing the user's avatar, a short bio, and a feed of only their posts. A poster's name on any post card links to their profile too. Bios live in the `bio` column on `users`; set them via the seeder or directly. The profile feed reuses the same post card as the main feed, so you can delete your own posts from there as well.

## Categories

A fixed, admin-curated set of categories (seeded with **Video Games, Reading, Movies/TV, Misc**). When composing a post, any logged-in user can tag it with one or more categories via pills in the composer. Category chips show on each post card and link to that category's feed.

- **Categories** dropdown in the nav lists all categories plus an "All categories" index at `/categories`.
- Each category has a feed at `/categories/{slug}` (slugs are auto-generated from the name).
- Categories are seeded as default app data (`CategorySeeder`, run automatically by `db:seed`), separate from the demo content.

## Admin

Admins are users with `is_admin = true`. The first/primary user (the one from `DEMO_USER_EMAIL`) is flagged admin by `DemoPostsSeeder`. Admin-only routes live under `/admin`, protected by a `can:admin` gate; an **Admin** link appears in the user menu for admins only.

- `/admin` — admin landing.
- `/admin/categories` — create, rename, and delete categories.

To make another user an admin, set the flag directly (e.g. `User::where('email', ...)->update(['is_admin' => true])`).

## Dark mode

There's a light/dark toggle in the nav (and the mobile menu). The choice is saved in `localStorage` and applied before first paint to avoid a flash, so it persists across visits and works for logged-out visitors too. Defaults to the OS preference on first visit. Implemented with Tailwind's `darkMode: 'class'` strategy.

## Demo data

To populate the feed with one post of every type (text, image, video, YouTube, and combos) for a demo:

```bash
docker compose exec -u 1000 app php artisan db:seed --class=DemoPostsSeeder
```

It creates the primary user plus a handful of named "geeks", each with a short bio and a few posts, and copies sample media from `database/seeders/assets/` into storage. It is **not** part of the default seed — run it explicitly. It's **idempotent**: re-running won't duplicate users or posts (it skips any user that already has posts).

The primary user's credentials come from the root `.env` (with built-in fallbacks), so you can change them without touching code:

```ini
DEMO_USER_NAME="Demo Geek"
DEMO_USER_EMAIL=demo@omnigeek.test
DEMO_USER_PASSWORD=password
```

Set `DEMO_USER_EMAIL` to your own email to make yourself the first user. The extra geeks are hard-coded in `DemoPostsSeeder` with `@omnigeek.test` emails and password `password` — local demo accounts only, never real credentials.

## Day-to-day

```bash
docker compose up -d                                  # start
docker compose down                                   # stop
docker compose run --rm node npm run dev              # asset watch during frontend work
docker compose exec -u 1000 app composer test         # run tests
```

## Tests

```bash
docker compose exec -u 1000 app composer test
```

Use `composer test`, not `php artisan test` directly. The `test` script sets `APP_ENV=testing` so the in-memory SQLite config and Livewire's test macros load — without it, the container's OS `APP_ENV=local` (injected from the root `.env`) shadows the testing env and Livewire assertions like `assertSeeVolt` fail. Test-only overrides live in `src/.env.testing`.

Covers invite gating (blocked without/with used invite, consumed on signup), post creation (text / YouTube parse / image upload), delete authorization, the YouTube URL parser, the demo seeder (multiple geeks, idempotency), and the geek profile pages (bio + own posts only, public reachability, delete authorization).

## Roadmap

- **Phase 2:** group chat to replace Discord, via Laravel Reverb (WebSockets). Separate feature, not started.
