<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            $model->slug = Str::slug($model->slugSource());
        });
    }

    protected function slugSource(): string
    {
        return $this->name;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Resolve by display name, creating if new — records with the same slug are shared.
    public static function firstOrCreateByName(string $name): static
    {
        return static::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        );
    }
}
