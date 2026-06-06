<?php

namespace App\Enums;

enum SubscriptionFrequency: string
{
    case Instant = 'instant';
    case Digest = 'digest';

    public function label(): string
    {
        return match ($this) {
            self::Instant => 'Instant — one email per post',
            self::Digest => 'Digest',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $f) => [$f->value => $f->label()])
            ->all();
    }
}
