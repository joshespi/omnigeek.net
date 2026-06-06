<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\Subscription;
use App\Notifications\Concerns\AppendsUnsubscribeFooter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPostPublished extends Notification implements ShouldQueue
{
    use AppendsUnsubscribeFooter;
    use Queueable;

    public function __construct(public Post $post, public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $preview = $this->post->preview(140) ?? 'New post from '.$this->post->user->name;

        $message = (new MailMessage)
            ->subject('New on '.config('app.name').': '.$this->post->user->name)
            ->line($preview)
            ->action('Read it', route('posts.show', $this->post));

        return $this->appendUnsubscribeFooter($message);
    }
}
