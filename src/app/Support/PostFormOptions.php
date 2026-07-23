<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Series;
use App\Models\Tag;

class PostFormOptions
{
    /**
     * The category/tag/series option sets shared by the compose and edit post forms.
     *
     * @return array{categories: \Illuminate\Support\Collection, tagHints: \Illuminate\Support\Collection, seriesHints: \Illuminate\Support\Collection}
     */
    public static function forRender(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'tagHints' => Tag::orderBy('name')->pluck('name'),
            'seriesHints' => Series::orderBy('name')->pluck('name'),
        ];
    }
}
