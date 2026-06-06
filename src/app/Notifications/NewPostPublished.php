<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewPostPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post, public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $preview = $this->post->body
            ? Str::limit($this->post->body, 140)
            : 'New post from '.$this->post->user->name;

        return (new MailMessage)
            ->subject('New on '.config('app.name').': '.$this->post->user->name)
            ->line($preview)
            ->action('Read it', route('posts.show', $this->post))
            ->line('Stop these emails any time:')
            ->line(route('subscriptions.unsubscribe', $this->subscription->token));
    }
}
