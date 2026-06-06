# Backlog

Loosely ordered. Top = next up.

## Subscription delivery — more channels & cadences

Email subscribe (instant, per-post) shipped. The `subscriptions` table already has `channel` and `frequency` columns as seams. Next:

- **Digest frequency**: weekly / monthly roundup instead of per-post. A scheduled command batches matching posts per subscriber and sends one email. (Add `last_notified_at` or similar.)
- **Web push via installable PWA**: add a manifest + service worker so the app is installable, then push notifications through it as another `channel`.
- **Discord channel**: post new entries to a Discord channel/webhook.
- **Social platforms**: cross-post to social media on publish.
- Prereq for real email delivery: a proper mailer (dev uses `MAIL_MAILER=log`) and a running queue worker.

## Deferred from earlier sessions

- Remove the leftover `test@example.com` user (from the default `DatabaseSeeder`) once it's confirmed unneeded.
- Set a real `DEMO_USER_PASSWORD` in the root `.env` instead of `password`.
- Always run tests as `-u 1000` (`docker compose exec -u 1000 app composer test`). Running as root leaves root-owned files under `storage/framework/testing/` that then break uid-1000 runs.

## Ideas / not scoped yet

- Phase 2 (original roadmap): group chat to replace Discord, via Laravel Reverb (WebSockets).
- Admin: expand beyond categories (user management, moderation) — `/admin` landing is built to hang more tools off of.
- Make asset URLs host-agnostic so `APP_URL` doesn't have to be edited per machine/IP.

## Done (recent)

- Email subscribe: public `/subscribe`, double opt-in (token confirm/unsubscribe), scoped by categories/geeks/tags (JSON filters, match ANY), queued fan-out to confirmed subscribers on publish.
- Dev `docker-compose.override.yml`: bind-mounts `./src` for live code + dev deps/tests, while the prod image stays lean (`--no-dev`, baked source).
- Tags: freeform lowercase hashtags in the composer (autocomplete hints), `#tag` chips, `/tags/{slug}` feeds, and a `/tags` browse/filter page (match ANY, shareable URL).
- Categories + admin panel (`is_admin` + `can:admin` gate, CRUD, composer pills, per-category feeds).
- Geeks per-user profiles (`/geeks/{user}`, bio + avatar + their posts).
- Root `.env` as deployment source of truth; app moved to port 8084.
- Dark-mode fixes: inputs, nav, profile page, persistence across `wire:navigate`.
