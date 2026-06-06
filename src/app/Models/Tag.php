<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    protected static function booted(): void
    {
        static::saving(fn (Tag $tag) => $tag->name = Str::lower(trim($tag->name)));
    }

    protected function slugSource(): string
    {
        return Str::lower(trim($this->name));
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Parse "#rust homelab #Self-Hosting" into normalised tags, creating any that are new.
     */
    public static function fromText(string $text): Collection
    {
        preg_match_all('/#?([\p{L}\p{N}][\p{L}\p{N}-]*)/u', $text, $matches);

        return collect($matches[1])
            ->map(fn ($name) => Str::lower($name))
            ->unique()
            ->reject(fn ($name) => $name === '')
            ->map(fn ($name) => static::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]));
    }
}
