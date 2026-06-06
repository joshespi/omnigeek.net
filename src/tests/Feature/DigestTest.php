<?php

namespace Tests\Feature;

use App\Enums\DigestCadence;
use App\Models\Post;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\DigestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_command_sends_to_digest_subscribers(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create(['created_at' => now()->subDays(3)]);

        $sub = Subscription::factory()->create([
            'frequency' => 'digest',
            'confirmed_at' => now(),
            'last_notified_at' => null,
        ]);

        $this->artisan('digest:send')->assertSuccessful();

        Notification::assertSentOnDemand(DigestNotification::class, function ($n, $channels, $notifiable) use ($sub) {
            return $notifiable->routes['mail'] === $sub->email;
        });
    }

    public function test_digest_skips_instant_subscribers(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create();

        Subscription::factory()->create([
            'frequency' => 'instant',
            'confirmed_at' => now(),
        ]);

        $this->artisan('digest:send')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_digest_reaches_subscribers_regardless_of_admin_cadence(): void
    {
        DigestCadence::Daily->store();
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create(['created_at' => now()->subHours(6)]);

        Subscription::factory()->create([
            'frequency' => 'digest',
            'confirmed_at' => now(),
            'last_notified_at' => null,
        ]);

        $this->artisan('digest:send')->assertSuccessful();

        Notification::assertSentOnDemand(
            DigestNotification::class,
            fn ($n) => $n->cadence === DigestCadence::Daily,
        );
    }

    public function test_daily_cadence_only_covers_the_last_day_for_new_subscribers(): void
    {
        DigestCadence::Daily->store();
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create(['created_at' => now()->subDays(3)]);

        Subscription::factory()->create([
            'frequency' => 'digest',
            'confirmed_at' => now(),
            'last_notified_at' => null,
        ]);

        $this->artisan('digest:send')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_digest_skips_if_no_posts_since_last_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create(['created_at' => now()->subDays(10)]);

        Subscription::factory()->create([
            'frequency' => 'digest',
            'confirmed_at' => now(),
            'last_notified_at' => now()->subDays(5),
        ]);

        $this->artisan('digest:send')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_digest_updates_last_notified_at(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Post::factory()->for($user)->create(['created_at' => now()->subDays(3)]);

        $sub = Subscription::factory()->create([
            'frequency' => 'digest',
            'confirmed_at' => now(),
            'last_notified_at' => null,
        ]);

        $this->artisan('digest:send');

        $this->assertNotNull($sub->fresh()->last_notified_at);
    }

    public function test_subscribe_form_saves_digest_frequency(): void
    {
        \Livewire\Livewire::test(\App\Livewire\Subscribe::class)
            ->set('email', 'digest@example.com')
            ->set('frequency', 'digest')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subscriptions', [
            'email' => 'digest@example.com',
            'frequency' => 'digest',
        ]);
    }
}
