<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemovalRequest extends Model
{
    // What the person wants changed. Keys are stored; labels are what the form shows.
    public const KINDS = [
        'name_in_image' => 'My name appears in an image',
        'name_in_text' => 'My name or my words appear in the text',
        'remove_image' => 'Remove an image I am in',
        'other' => 'Something else',
    ];

    protected $fillable = ['post_id', 'name', 'email', 'kind', 'details', 'resolved_at'];

    protected $casts = ['resolved_at' => 'datetime'];

    // The post may be a meme, which the default main-feed scope would hide.
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->anyFeed();
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('resolved_at');
    }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    public function kindLabel(): string
    {
        return self::KINDS[$this->kind] ?? $this->kind;
    }
}
