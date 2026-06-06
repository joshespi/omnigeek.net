# Backlog

Loosely ordered. Top = next up.

## Tags (phases 2 & 3 of the tagging work)

Categories (fixed, admin-curated) shipped. Tags are the freeform half.

### Phase 2 — freeform hashtags

- `tags` table (`id`, `name`, `slug`) + `post_tag` pivot.
- Store tag text **lowercase** so `#Rust` and `#rust` collapse to one.
- Composer: a text field where the poster types hashtags; parse on save, auto-create tags that don't exist, attach.
- Autocomplete hints in the composer showing existing tags as the poster types, to cut down on near-duplicate forms of the same word.
- Render `#tag` chips on post cards, each linking to that tag's feed.
- Tags dropdown in the nav.
- Tests + README.

### Phase 3 — tag filter / browse page

- `/tags` index: clickable tag cloud/list.
- Filter view: pick one or more tags, see the matching feed.
- Match logic: **ANY** — posts carrying at least one of the selected tags. Matching is by exact (lowercased) tag text.
- Tests + README.

## Email subscribe (new-post notifications)

- Public form: visitor enters email to subscribe to new-post notifications.
- **Double opt-in**: on submit, send a confirmation email with a verify link; only confirmed addresses receive notifications. Store a token + `confirmed_at`.
- Subscriber chooses scope at signup: everything, or filtered by specific categories / geeks / tags. Lets each subscriber tune what lands in their inbox. Pairs with the existing category/geek/tag models — store the selected filters with the subscription.
- Send a notification when a new post is published (queued, not inline with the request).
- Unsubscribe link in every email (one-click, token-based).
- Prereqs: a real mailer (currently `MAIL_MAILER=log`) and the queue running for sends.
- Tests + README.

## Deferred from earlier sessions

- Remove the leftover `test@example.com` user (from the default `DatabaseSeeder`) once it's confirmed unneeded.
- Set a real `DEMO_USER_PASSWORD` in the root `.env` instead of `password`.
- Always run tests as `-u 1000` (`docker compose exec -u 1000 app composer test`). Running as root leaves root-owned files under `storage/framework/testing/` that then break uid-1000 runs.

## Ideas / not scoped yet

- Phase 2 (original roadmap): group chat to replace Discord, via Laravel Reverb (WebSockets).
- Admin: expand beyond categories (user management, moderation) — `/admin` landing is built to hang more tools off of.
- Make asset URLs host-agnostic so `APP_URL` doesn't have to be edited per machine/IP.

## Done (recent)

- Categories + admin panel (`is_admin` + `can:admin` gate, CRUD, composer pills, per-category feeds).
- Geeks per-user profiles (`/geeks/{user}`, bio + avatar + their posts).
- Root `.env` as deployment source of truth; app moved to port 8084.
- Dark-mode fixes: inputs, nav, profile page, persistence across `wire:navigate`.
