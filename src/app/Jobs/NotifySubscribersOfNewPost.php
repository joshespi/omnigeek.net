<?php

namespace App\Jobs;

use App\Enums\SubscriptionFrequency;
use App\Models\Post;
use App\Models\Subscription;
use App\Notifications\NewPostPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class NotifySubscribersOfNewPost implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post) {}

    public function handle(): void
    {
        $this->post->loadMissing('user', 'categories', 'tags');

        Subscription::forEmailDelivery(SubscriptionFrequency::Instant)
            ->cursor()
            ->filter(fn (Subscription $sub) => $sub->wantsPost($this->post))
            ->each(function (Subscription $sub) {
                Notification::route('mail', $sub->email)
                    ->notify(new NewPostPublished($this->post, $sub));
            });
    }
}
