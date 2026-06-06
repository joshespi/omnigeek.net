<?php

namespace App\Console\Commands;

use App\Enums\DigestCadence;
use App\Enums\SubscriptionFrequency;
use App\Models\Post;
use App\Models\Subscription;
use App\Notifications\DigestNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

#[Signature('digest:send')]
#[Description('Send the digest to non-instant subscribers on the active cadence')]
class SendDigest extends Command
{
    public function handle(): int
    {
        $now = now();
        $cadence = DigestCadence::current();

        $subscribers = Subscription::forEmailDelivery(SubscriptionFrequency::Digest)->get();

        if ($subscribers->isEmpty()) {
            $this->info('Digest queued for 0 subscriber(s).');

            return self::SUCCESS;
        }

        $cutoffFor = fn (Subscription $sub): Carbon => $sub->last_notified_at ?? $cadence->windowStart($now);

        $candidates = Post::withFeedRelations()
            ->where('created_at', '>', $subscribers->min($cutoffFor))
            ->latest()
            ->get();

        $sent = $subscribers->filter(function (Subscription $sub) use ($candidates, $cutoffFor, $cadence, $now) {
            $posts = $candidates
                ->where('created_at', '>', $cutoffFor($sub))
                ->filter(fn (Post $post) => $sub->wantsPost($post))
                ->values();

            if ($posts->isEmpty()) {
                return false;
            }

            Notification::route('mail', $sub->email)
                ->notify(new DigestNotification($posts, $sub, $cadence));

            $sub->update(['last_notified_at' => $now]);

            return true;
        })->count();

        $this->info("Digest queued for {$sent} subscriber(s).");

        return self::SUCCESS;
    }
}
