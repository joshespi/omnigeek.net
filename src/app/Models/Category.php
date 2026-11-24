<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasSlug;

    protected $fillable = ['name', 'slug'];

    public static function forNavigation(): Collection
    {
        return once(static fn () => static::orderBy('name')->get(['slug', 'name']));
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
