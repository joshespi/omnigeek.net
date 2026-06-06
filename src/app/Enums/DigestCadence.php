<?php

namespace App\Enums;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

enum DigestCadence: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    private const CACHE_KEY = 'digest_cadence';

    public static function current(): self
    {
        return self::tryFrom(Cache::get(self::CACHE_KEY, '')) ?? self::Weekly;
    }

    public function store(): void
    {
        Cache::forever(self::CACHE_KEY, $this->value);
    }

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'Daily (8am)',
            self::Weekly => 'Weekly (Mondays 8am)',
            self::Monthly => 'Monthly (1st, 8am)',
        };
    }

    public function noun(): string
    {
        return $this->value;
    }

    public function windowStart(Carbon $now): Carbon
    {
        return match ($this) {
            self::Daily => $now->copy()->subDay(),
            self::Weekly => $now->copy()->subWeek(),
            self::Monthly => $now->copy()->subMonth(),
        };
    }

    public function schedule(Event $event): void
    {
        match ($this) {
            self::Daily => $event->dailyAt('08:00'),
            self::Weekly => $event->weekly()->mondays()->at('08:00'),
            self::Monthly => $event->monthlyOn(1, '08:00'),
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
