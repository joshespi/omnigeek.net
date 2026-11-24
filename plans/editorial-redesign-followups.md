# Editorial redesign: follow-up refactors

These came out of the /espi-review of the editorial redesign. Each one is too big for that review. Each section stands alone. Paths are relative to `src/`.

## 1. One colour-token system

- **Why:** two accent systems ship side by side.
  - `brand-*` in `tailwind.config.js` hand-copies `--editorial-accent`: brand-600 is the light accent and brand-400 the dark one.
  - Views pair `text-brand-600 dark:text-brand-400` about 33 times. Success and error text is patched per view (`text-green-700 dark:text-green-300`, `text-red-700 dark:text-red-300`).
  - About 30 views still use `gray-*` / `dark:gray-*`.
  - The surface-panel string `rounded-xl border border-editorial-border bg-editorial-surface` is repeated 13 times.
- **Where:**
  - `resources/css/app.css` (`:root`, `:root.dark`, `@layer components`) and `tailwind.config.js`.
  - Views:
    - `components/message-page`, `livewire/request-removal`, `livewire/series-feed`, `livewire/subscribe`, `profile.blade.php` (4 panels)
    - `layouts/app` (compose modal header), `layouts/guest`, `livewire/geek-profile`, `partials/post-card`, `partials/post-gallery`
    - `components/auth-session-status`, `livewire/pages/auth/*`, `partials/post-moderation`, `livewire/profile/*`, `admin/*`
  - Palette mirrors: `public/offline.html` and `errors/minimal.blade.php`.
- **Proposed approach:**
  - Add CSS-var tokens for `accent`, `accent-strong` (solid buttons), `danger` and `success`, each with a dark value. Then replace the `dark:` pairs with single classes.
  - Shrink `brand` to only the shades still needed.
  - Add a `.editorial-panel` component class, and build `.editorial-card` and `.editorial-empty` on top of it.
  - Finish the `gray-*` sweep, or remap Tailwind `gray` to the editorial vars.
  - Optionally move the `:root` var block into one static `public/theme-vars.css` that offline.html and minimal.blade link, together with a shared `public/theme.js` (dark-mode-head and offline.html both carry the theme script).
- **Risks / open questions:**
  - Remapping `gray` changes every admin page at once; a manual sweep is slower but controlled.
  - offline.html is cached by `sw.js`, so a new static CSS file must be added to the precache list.
- **Suggested scope:** tokens, the panel class, and the touched public views. Admin views can be a second pass.

## 2. Lean eager-loading for card feeds

- **Why:**
  - `Feed.php` and `ShowPost::relatedPosts()` inline `with('user','categories','media')->latestForFeed()->latest('id')`.
  - The other six card feeds still use `withFeedRelations()`. It also loads `tags` and `series`, which `editorial-card` never renders, so each render runs 2 wasted queries.
  - Only these two places get the `id` tie-break.
- **Where:**
  - `app/Models/Post.php` (`scopeWithFeedRelations`, `scopeLatestForFeed`).
  - `app/Livewire/{Feed,ShowPost,CategoryFeed,GeekProfile,TagFeed,Search,SeriesFeed,TagIndex}.php`.
- **Proposed approach:**
  - Add `scopeWithCardRelations()` (`user`, `categories`, `media`) and use it in every editorial-card feed.
  - Keep `withFeedRelations` for `MemeFeed`, which still renders tags through post-card.
  - Move the `id` tie-break into `scopeLatestForFeed`.
- **Risks / open questions:** the tie-break changes the order of posts with equal dates in all feeds. Check the tests that assert order.
- **Suggested scope:** the model scopes and the 8 Livewire classes, nothing else.

## 3. Home "featured post" state

- **Why:** "first post on page 1 renders large" is spread over three places:
  - `Feed.php` sets `featured`.
  - `livewire/feed.blade.php` computes `! $featured || $posts->count() > 1 || $posts->hasPages()`.
  - `components/posts-feed` has a `skipFirst` prop, `getCollection()->skip(1)` and `@unless($skipFirst)` around the empty state.
- **Where:** `app/Livewire/Feed.php`, `resources/views/livewire/feed.blade.php`, `resources/views/components/posts-feed.blade.php`.
- **Proposed approach:** a `featureFirst` flag on `posts-feed` that renders `$loop->first && $posts->onFirstPage()` as `<x-editorial-card featured>`. Remove `featured`, `skipFirst` and the visibility condition.
- **Risks / open questions:** the featured card currently sits outside the "Latest posts" section, under its own heading. Merging changes the heading structure and the grid layout, so decide on the markup first.
- **Suggested scope:** these three files and `tests/Feature/FeedTest.php`.

## 4. Two card renderers (`post-card` vs `editorial-card`)

- **Why:** `posts-feed` switches between the old `partials/post-card` and `editorial-card` on a prop named `deletable`, which only the meme feed passes. The two cards must be kept in step: byline, categories, tags, and the editor mount. The editor is now mounted in three places (post-card, editorial-card, show-post).
- **Where:** `components/posts-feed.blade.php`, `livewire/meme-feed.blade.php`, `partials/post-card.blade.php`, `components/editorial-card.blade.php` (`editable` prop), `livewire/show-post.blade.php` (`@php($canEdit …)`).
- **Proposed approach:**
  - The meme feed loops post-card itself, and `deletable` goes away.
  - Pass `canEdit` from `ShowPost::render()`.
  - Decide whether inline editing stays on feed cards at all.
  - Add an `x-post-tags` component that mirrors `x-post-categories`, and an `avatar` option on `x-post-byline`.
- **Risks / open questions:** this is a product decision: is inline editing from feeds still wanted?
- **Suggested scope:** the card and feed views only, with no model changes.

## 5. Split `post-gallery` cover mode

- **Why:** the `cover` flag adds about 9 branches to `partials/post-gallery.blade.php` (grid, tile classes, badge, captions, preload). It still builds the full grid to show one item plus a "+N" badge.
- **Where:** `partials/post-gallery.blade.php`, `components/editorial-card.blade.php`.
- **Proposed approach:** a separate `partials/post-cover.blade.php` that shows the first item and the count badge and opens the same lightbox. `post-gallery` keeps only the grid.
- **Risks / open questions:** sharing the lightbox (Alpine state) between the two partials; it may need a small shared component.
- **Suggested scope:** the two partials and the card.

## 6. Related posts query and caching

- **Why:**
  - `ShowPost::relatedPosts()` builds `whereHas(categories) OR whereHas(tags)`. That gives two correlated EXISTS subqueries joined by OR, which index poorly.
  - It runs on every render of the busiest page, including Livewire round-trips.
  - The query lives in the component, not the model.
- **Where:** `app/Livewire/ShowPost.php`, `app/Models/Post.php`.
- **Proposed approach:**
  - Add a `Post::relatedTo(Post $post)` scope. It collects candidate IDs with a `union` over `category_post` and `post_tag`, then runs `whereKey($ids)->indexable()->latestForFeed()->limit(3)`.
  - Cache the result per post (`Cache::remember("post:{id}:related", …)`), or at least compute it once in `mount()`.
- **Risks / open questions:** when to invalidate the cache on post save or delete; a TTL may be enough.
- **Suggested scope:** the model scope, the component, and one test.

## 7. Plain-text excerpts without parsing Markdown per card

- **Why:** `editorial-card` calls `preview()` and `plainBody()`, which run a full CommonMark parse per card just to show 150–235 characters. That is about 16 parses on the home page and 3 more on each post page.
- **Where:** `app/Models/Post.php` (`plainBody`, `preview`, the `saving` hook), `components/editorial-card.blade.php`, and a new migration.
- **Proposed approach:**
  - Add an `excerpt` text column, filled in `Post::saving` from `plainBody()`. Backfill it in the migration.
  - The card reads the column.
  - Also add `Post::excerpt(int)` and `Post::wasUpdatedAfterPublishing()` (the rule currently in `components/post-byline`), so that logic lives on the model.
- **Risks / open questions:** a migration plus a backfill of every post, and the column must be kept in sync when posts are edited.
- **Suggested scope:** the column, the hook, the backfill, and the card.

## 8. Navigation data: caching and loading

- **Why:**
  - Categories, geeks and tags are queried on every page load.
  - The loading is inconsistent: categories come from `Category::forNavigation()`, called straight from Blade with a per-request `once()`, while geeks and tags are Volt `#[Computed]`.
- **Where:** `livewire/layout/navigation.blade.php`, `components/category-filter.blade.php`, `app/Models/Category.php`, and `components/site-logo.blade.php` (its `once()` should live in `SiteMedia::logoUrl()`).
- **Proposed approach:**
  - A View Composer, or shared cached values, for the nav lists.
  - `Cache::rememberForever` cleared on model save and delete, or a short TTL for geeks and tags.
- **Risks / open questions:** stale menus if invalidation is missed.
- **Suggested scope:** the nav data only.

## 9. Self-host fonts

- **Why:** `partials/editorial-fonts` loads a render-blocking third-party stylesheet from fonts.bunny.net, and first paint waits on that round-trip.
- **Where:** `partials/editorial-fonts.blade.php`, `layouts/app`, `layouts/guest`, `resources/css/app.css`, `package.json`.
- **Proposed approach:** use `@fontsource-variable/inter` and `@fontsource-variable/space-grotesk` through Vite, with a `<link rel=preload as=font crossorigin>` for Inter.
- **Risks / open questions:** bundle size, and whether `sw.js` should precache the font files.
- **Suggested scope:** the fonts only.

## Optional / low payoff

- **`x-page-header` component:** an `h1.page-title` plus a meta line is repeated in 7 feed views.
- **Search input in `livewire/search.blade.php`:** it hand-copies the `x-text-input` classes but uses `bg-editorial-surface`. Unify it once the tokens settle.
