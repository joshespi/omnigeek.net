<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'body', 'media_path', 'media_type', 'youtube_id'];

    protected $casts = ['view_count' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWithFeedRelations(Builder $query): Builder
    {
        return $query->with('user', 'categories', 'tags');
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
