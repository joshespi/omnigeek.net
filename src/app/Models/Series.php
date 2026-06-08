<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Series extends Model
{
    use HasFactory;
    use HasSlug;

    protected $table = 'series';

    protected $fillable = ['name', 'slug'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
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
        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => trim($name)],
        );
    }
}
