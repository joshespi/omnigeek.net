<?php

namespace App\Jobs;

use App\Enums\SubscriptionFrequency;
use App\Models\Post;
use App\Models\Subscription;
use App\Notifications\NewPostPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class NotifySubscribersOfNewPost implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post) {}

    public function handle(): void
    {
        $this->post->loadMissing('user', 'categories', 'tags');

        // notify() on a ShouldQueue notification dispatches its own queued job
        // immediately, so a mid-loop failure here (worker runs --tries=3) would
        // otherwise re-notify subscribers already handled by the earlier attempt.
        // Track who's been sent so a retry only covers what's left.
        $sentKey = "notified-subscribers:post:{$this->post->id}";
        $sent = Cache::get($sentKey, []);

        Subscription::forEmailDelivery(SubscriptionFrequency::Instant)
            ->cursor()
            ->filter(fn (Subscription $sub) => ! in_array($sub->id, $sent, true) && $sub->wantsPost($this->post))
            ->each(function (Subscription $sub) use ($sentKey, &$sent) {
                Notification::route('mail', $sub->email)
                    ->notify(new NewPostPublished($this->post, $sub));

                $sent[] = $sub->id;
                Cache::put($sentKey, $sent, now()->addDay());
            });

        Cache::forget($sentKey);
    }
}
