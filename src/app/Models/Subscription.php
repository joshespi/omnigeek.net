<?php

namespace App\Models;

use App\Enums\SubscriptionFrequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = ['email', 'token', 'confirmed_at', 'channel', 'frequency', 'filters', 'last_notified_at'];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
            'last_notified_at' => 'datetime',
            'filters' => 'array',
            'frequency' => SubscriptionFrequency::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscription $subscription) {
            $subscription->token ??= Str::random(48);
        });
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function scopeForEmailDelivery(Builder $query, SubscriptionFrequency $frequency): Builder
    {
        return $query->confirmed()
            ->where('channel', 'email')
            ->where('frequency', $frequency->value);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    /**
     * Does this subscription want to hear about the given post?
     * No filters = everything. Otherwise match ANY selected category/geek/tag.
     */
    public function wantsPost(Post $post): bool
    {
        $filters = $this->filters ?? [];

        $categories = $filters['categories'] ?? [];
        $geeks = $filters['geeks'] ?? [];
        $tags = $filters['tags'] ?? [];

        if (! $categories && ! $geeks && ! $tags) {
            return true;
        }

        return $post->categories->pluck('slug')->intersect($categories)->isNotEmpty()
            || $post->tags->pluck('slug')->intersect($tags)->isNotEmpty()
            || in_array($post->user_id, $geeks, false);
    }
}
