<?php

namespace App\Models;

use App\Enums\Feed;
use App\Models\Scopes\MainFeedScope;
use App\Observers\PostObserver;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(PostObserver::class)]
class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'body', 'youtube_id', 'published_at', 'feed', 'nsfw', 'series_id', 'series_part'];

    protected $casts = ['view_count' => 'integer', 'published_at' => 'datetime', 'feed' => Feed::class, 'nsfw' => 'boolean', 'series_part' => 'integer'];

    // Mirror the DB defaults so a freshly created model reports them without a refresh.
    protected $attributes = ['feed' => Feed::Main->value, 'nsfw' => false];

    // plainBody() memo: [source body, plain text]. Keyed on the body so an edit
    // within the same request can't return stale text.
    private ?array $plainBodyCache = null;

    protected static function booted(): void
    {
        // Lists default to the main feed; memes opt in. Single-post resolution
        // overrides this (see resolveRouteBinding) so shared meme URLs still work.
        static::addGlobalScope(new MainFeedScope);

        // Delete children in PHP (not via DB FK cascade) so PostMedia::deleting
        // fires for each and removes its stored file. Covers user-cascade deletes too.
        static::deleting(fn (Post $post) => $post->media->each->delete());

        // Feed-dependent invariants, enforced on every write path (not just the compose
        // form) so a direct update — e.g. moveToOtherFeed() flipping the feed — can't
        // leave inconsistent state. NSFW is meme-only; series are main-feed-only.
        static::saving(function (Post $post) {
            if ($post->feed === Feed::Memes) {
                $post->series_id = null;
                $post->series_part = null;
            } else {
                $post->nsfw = false;
            }

            // A part number is meaningless without a series to order within.
            if (! $post->series_id) {
                $post->series_part = null;
            }
        });
    }

    // Route + Livewire model binding resolve a specific post by key — never hide
    // a meme there, or shared meme URLs and meme deletion would 404.
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->resolveRouteBindingQuery($this, $value, $field)
            ->withoutGlobalScope(MainFeedScope::class)
            ->first();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('sort_order');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function scopeWithFeedRelations(Builder $query): Builder
    {
        return $query->with('user', 'categories', 'tags', 'media', 'series');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        });
    }

    // Drop the feed filter entirely — admin views that list every post.
    public function scopeAnyFeed(Builder $query): Builder
    {
        return $query->withoutGlobalScope(MainFeedScope::class);
    }

    // Filter to a specific feed regardless of the default scope.
    public function scopeOfFeed(Builder $query, Feed $feed): Builder
    {
        return $query->anyFeed()->where('feed', $feed->value);
    }

    // Swap the default main-feed scope for the memes feed (the /memes page).
    public function scopeMemes(Builder $query): Builder
    {
        return $query->ofFeed(Feed::Memes);
    }

    // Shared feed ordering: scheduled posts sort by publish date, others by creation.
    public function scopeLatestForFeed(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, created_at) DESC');
    }

    public function displayDate(): \Illuminate\Support\Carbon
    {
        return $this->published_at ?? $this->created_at;
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        // MySQL/MariaDB (production) gets real full-text ranking via FULLTEXT + MATCH/AGAINST
        // (see the fulltext-index migration). sqlite (tests, local dev) has no equivalent,
        // so it falls back to LIKE — behaviourally close enough for those environments.
        if ($query->getConnection()->getDriverName() !== 'mysql') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $term).'%';

            return $query->where(function (Builder $q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('body', 'like', $like)
                  ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
            });
        }

        // BOOLEAN MODE + a trailing wildcard per word approximates "contains" matching
        // (unlike NATURAL LANGUAGE MODE, it also works on words below MySQL's default
        // 50%-of-rows relevance threshold). Note InnoDB's default innodb_ft_min_token_size
        // (3) still means 1-2 char words never match — this only helps words of 3+ chars.
        $boolean = collect(preg_split('/\s+/', trim($term)))
            ->map(fn (string $word) => preg_replace('/[+\-<>()~*"@]/', '', $word))
            ->filter(fn (string $word) => $word !== '')
            ->map(fn (string $word) => '+'.$word.'*')
            ->implode(' ');

        return $query->where(function (Builder $q) use ($boolean, $term) {
            // Every word was pure punctuation (e.g. "??") — nothing left for MATCH to
            // take, so skip straight to the author-name fallback instead of erroring.
            if ($boolean !== '') {
                $q->whereRaw('MATCH (title, body) AGAINST (? IN BOOLEAN MODE)', [$boolean]);
            }

            $q->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', '%'.$term.'%'));
        });
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function canDelete(?User $user): bool
    {
        return $user && ($this->user_id === $user->id || $user->isAdmin());
    }

    // Owner only — an admin edits content through the dedicated admin panel
    // (AdminPosts, gated by the 'admin' Gate), not the inline feed editor.
    public function canEdit(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }

    // The feed a main post would move to, and vice versa.
    public function oppositeFeed(): Feed
    {
        return $this->feed === Feed::Memes ? Feed::Main : Feed::Memes;
    }

    public function isPublished(): bool
    {
        return $this->published_at === null || $this->published_at->lte(now());
    }

    // What search engines may index: live and not click-through-gated. Shared by the
    // sitemap (scope) and the post page's robots meta (instance) so they can't drift.
    public function scopeIndexable(Builder $query): Builder
    {
        return $query->published()->where('nsfw', false);
    }

    public function isIndexable(): bool
    {
        return $this->isPublished() && ! $this->nsfw;
    }

    // Query-builder increment: Eloquent's would also bump updated_at, which feeds the
    // sitemap lastmod and JSON-LD dateModified; anyFeed() so memes count too.
    public function recordView(): void
    {
        static::anyFeed()->whereKey($this->getKey())->toBase()->increment('view_count');
    }

    // Scheduled posts are visible only to whoever could edit or moderate them.
    public function canView(?User $user): bool
    {
        return $this->isPublished() || $this->canDelete($user);
    }

    // Body as plain text — Markdown rendered, then tags stripped — for meta
    // descriptions and structured data, where raw `**syntax**` would leak through.
    // Memoized: previews of this post are read from several places per page.
    public function plainBody(): string
    {
        if (! $this->body) {
            return '';
        }

        if ($this->plainBodyCache && $this->plainBodyCache[0] === $this->body) {
            return $this->plainBodyCache[1];
        }

        // Swap images for their alt text first, so an image-only body still has words.
        $html = preg_replace('/<img\b[^>]*\balt="([^"]*)"[^>]*>/i', ' $1 ', Markdown::render($this->body));
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5)));

        $this->plainBodyCache = [$this->body, $text];

        return $text;
    }

    public function preview(int $limit): ?string
    {
        if ($this->title) {
            return $this->title;
        }

        $plain = $this->plainBody();

        return $plain !== '' ? str($plain)->limit($limit)->toString() : null;
    }

    public static function parseYoutubeId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return $m[1];
        }

        return preg_match('~^[\w-]{11}$~', $url) ? $url : null;
    }
}
