<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Volt\Volt;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_six_failed_attempts_lock_the_account_out(): void
    {
        Event::fake([Lockout::class]);
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        // RateLimiter allows 5; the 6th trips ensureIsNotRateLimited().
        for ($i = 0; $i < 6; $i++) {
            $component->call('login');
        }

        // Throttle message replaces the plain auth.failed error.
        $this->assertStringContainsString(
            'Too many login attempts',
            collect($component->errors()->get('form.email'))->implode(' ')
        );
        Event::assertDispatched(Lockout::class);
        $this->assertGuest();
    }
}
