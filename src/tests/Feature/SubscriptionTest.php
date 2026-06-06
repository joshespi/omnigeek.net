<?php

namespace Tests\Feature;

use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\Feed;
use App\Livewire\Subscribe;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\ConfirmSubscription;
use App\Notifications\NewPostPublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribing_creates_an_unconfirmed_record_and_sends_a_confirmation(): void
    {
        Notification::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'reader@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('done', true);

        $sub = Subscription::where('email', 'reader@example.com')->first();
        $this->assertNotNull($sub);
        $this->assertNull($sub->confirmed_at);
        Notification::assertSentOnDemand(ConfirmSubscription::class);
    }

    public function test_confirm_link_confirms_the_subscription(): void
    {
        $sub = Subscription::create(['email' => 'reader@example.com']);

        $this->get(route('subscriptions.confirm', $sub->token))->assertOk();

        $this->assertNotNull($sub->fresh()->confirmed_at);
    }

    public function test_unsubscribe_link_deletes_the_subscription(): void
    {
        $sub = Subscription::create(['email' => 'reader@example.com', 'confirmed_at' => now()]);

        $this->get(route('subscriptions.unsubscribe', $sub->token))->assertOk();

        $this->assertModelMissing($sub);
    }

    public function test_publishing_a_post_dispatches_the_notify_job(): void
    {
        Queue::fake();

        Livewire::actingAs(User::factory()->create())
            ->test(Feed::class)
            ->set('body', 'hello subscribers')
            ->call('save')
            ->assertHasNoErrors();

        Queue::assertPushed(NotifySubscribersOfNewPost::class);
    }

    public function test_only_confirmed_subscribers_are_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Subscription::create(['email' => 'confirmed@example.com', 'confirmed_at' => now()]);
        Subscription::create(['email' => 'pending@example.com']);

        $post = $user->posts()->create(['body' => 'a post']);
        (new NotifySubscribersOfNewPost($post))->handle();

        Notification::assertSentOnDemand(
            NewPostPublished::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'confirmed@example.com'
        );
        Notification::assertSentOnDemandTimes(NewPostPublished::class, 1);
    }

    public function test_filters_limit_who_gets_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $games = Category::create(['name' => 'Video Games']);
        $rust = Tag::fromText('rust')->first();

        Subscription::create(['email' => 'cat@example.com', 'confirmed_at' => now(), 'filters' => ['categories' => [$games->slug]]]);
        Subscription::create(['email' => 'tag@example.com', 'confirmed_at' => now(), 'filters' => ['tags' => [$rust->slug]]]);
        Subscription::create(['email' => 'everything@example.com', 'confirmed_at' => now()]);
        Subscription::create(['email' => 'other@example.com', 'confirmed_at' => now(), 'filters' => ['tags' => ['unrelated']]]);

        $post = $user->posts()->create(['body' => 'tagged post']);
        $post->tags()->attach($rust);

        (new NotifySubscribersOfNewPost($post))->handle();

        // rust-tag subscriber + everything subscriber = 2; category and unrelated-tag subscribers excluded
        Notification::assertSentOnDemandTimes(NewPostPublished::class, 2);
    }
}
