<?php

namespace Tests\Unit;

use App\Enums\DigestCadence;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class DigestCadenceTest extends TestCase
{
    public function test_current_falls_back_to_weekly_when_the_cache_store_is_unavailable(): void
    {
        Cache::shouldReceive('get')->andThrow(new RuntimeException('cache table missing'));

        $this->assertSame(DigestCadence::Weekly, DigestCadence::current());
    }

    public function test_current_falls_back_to_weekly_for_an_unrecognized_value(): void
    {
        Cache::shouldReceive('get')->andReturn('fortnightly');

        $this->assertSame(DigestCadence::Weekly, DigestCadence::current());
    }

    public function test_current_returns_the_stored_cadence(): void
    {
        Cache::shouldReceive('get')->andReturn('daily');

        $this->assertSame(DigestCadence::Daily, DigestCadence::current());
    }
}
