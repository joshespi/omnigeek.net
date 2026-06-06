# Backlog

Loosely ordered. Top = next up.

## Subscription delivery — more channels & cadences

Email subscribe (instant, per-post) shipped. The `subscriptions` table already has `channel` and `frequency` columns as seams. Next:

- **Digest frequency**: weekly / monthly roundup instead of per-post. A scheduled command batches matching posts per subscriber and sends one email. (Add `last_notified_at` or similar.)
- **Web push via installable PWA**: add a manifest + service worker so the app is installable, then push notifications through it as another `channel`.
- **Discord channel**: post new entries to a Discord channel/webhook.
- **Social platforms**: cross-post to social media on publish.
- Prereq for real email delivery: a proper mailer (dev uses `MAIL_MAILER=log`) and a running queue worker.

## Deferred / known issues

- **Prod `.env` cleanup**: `APP_KEY` was empty on omnigeek.net (generate with `docker compose run --rm app php artisan key:generate --show` and set it). `DEMO_USER_*` env vars are stale — rename to `INITIAL_USER_*` to match current config.
- **Prod assets**: `public/build/` missing on omnigeek.net — run `git pull` then rebuild image. Dockerfile now fails loudly if Vite produces no manifest, so this won't silently recur on future builds.
- **`ShowPost::deletePost` duplication**: the method is a manual copy of `HandlesPostDeletion` plus a redirect. The trait has no redirect mechanism, so it wasn't merged during simplify. If the trait gains a post-delete hook, clean this up.
- Always run tests as `-u 1000` (`docker compose exec -u 1000 app composer test`). Root-run tests leave root-owned files under `storage/framework/testing/` that break subsequent uid-1000 runs.

## Ideas / not scoped yet

- Phase 2 (original roadmap): group chat to replace Discord, via Laravel Reverb (WebSockets).
- Make asset URLs host-agnostic so `APP_URL` doesn't have to be edited per machine/IP.
