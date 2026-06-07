<?php

namespace App\Models\Scopes;

use App\Enums\Feed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Default every Post *list* query to the main feed. Memes are opt-in via
 * Post::memes() / withoutGlobalScope(MainFeedScope::class). Single-post
 * resolution (route + Livewire model binding) bypasses this via
 * Post::resolveRouteBinding so shared meme URLs still resolve.
 */
class MainFeedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable().'.feed', Feed::Main->value);
    }
}
