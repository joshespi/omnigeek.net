<?php

namespace App\Models;

use App\Enums\Feed;
use App\Models\Scopes\MainFeedScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'body', 'youtube_id', 'published_at', 'feed', 'nsfw'];

    protected $casts = ['view_count' => 'integer', 'published_at' => 'datetime', 'feed' => Feed::class, 'nsfw' => 'boolean'];

    // Mirror the DB defaults so a freshly created model reports them without a refresh.
    protected $attributes = ['feed' => Feed::Main->value, 'nsfw' => false];

    protected static function booted(): void
    {
        // Lists default to the main feed; memes opt in. Single-post resolution
        // overrides this (see resolveRouteBinding) so shared meme URLs still work.
        static::addGlobalScope(new MainFeedScope);

        // Delete children in PHP (not via DB FK cascade) so PostMedia::deleting
        // fires for each and removes its stored file. Covers user-cascade deletes too.
        static::deleting(fn (Post $post) => $post->media->each->delete());

        // NSFW only applies to memes — enforce the invariant on every write path,
        // not just the compose form, so a direct update can't mark a main post NSFW.
        static::saving(function (Post $post) {
            if ($post->feed !== Feed::Memes) {
                $post->nsfw = false;
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

    public function scopeWithFeedRelations(Builder $query): Builder
    {
        return $query->with('user', 'categories', 'tags', 'media');
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
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('body', 'like', $like)
              ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
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

    public function canEdit(?User $user): bool
    {
        return $user && $this->user_id === $user->id;
    }

    // The feed a main post would move to, and vice versa.
    public function oppositeFeed(): Feed
    {
        return $this->feed === Feed::Memes ? Feed::Main : Feed::Memes;
    }

    public function preview(int $limit): ?string
    {
        return $this->title ?: ($this->body ? str($this->body)->limit($limit)->toString() : null);
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
