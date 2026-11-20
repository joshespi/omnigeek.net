<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\Markdown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'series';

    protected $fillable = ['name', 'slug', 'description'];

    // The slug is set once, from the first name. Renaming must not move the URL or
    // orphan subscriptions, which store series by slug.
    protected function slugSource(): string
    {
        return $this->slug ?: $this->name;
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Series with something published to read, by name.
    public function scopeListed(Builder $query): Builder
    {
        return $query->whereHas('posts', fn ($q) => $q->published())->orderBy('name');
    }

    // The introduction as plain text, cut to $limit characters.
    public function excerpt(int $limit): string
    {
        return $this->description ? str(Markdown::plain($this->description))->limit($limit)->toString() : '';
    }

    // Posts in reading order: by part number, unnumbered last, then by date.
    public function postsInOrder(): HasMany
    {
        return $this->posts()
            ->orderByRaw('series_part IS NULL, series_part')
            ->orderByRaw('COALESCE(published_at, created_at)');
    }

    // Resolve a series by its display name, creating it if new. Series are shared,
    // so two authors typing the same name land on the same series.
    public static function firstByName(string $name): self
    {
        return static::firstOrCreateByName(trim($name));
    }
}
