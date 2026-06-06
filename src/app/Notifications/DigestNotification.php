<?php

namespace App\Notifications;

use App\Enums\DigestCadence;
use App\Models\Subscription;
use App\Notifications\Concerns\AppendsUnsubscribeFooter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DigestNotification extends Notification implements ShouldQueue
{
    use AppendsUnsubscribeFooter;
    use Queueable;

    public function __construct(
        public Collection $posts,
        public Subscription $subscription,
        public DigestCadence $cadence,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->posts->count();

        $message = (new MailMessage)
            ->subject('Your '.$this->cadence->noun().' '.config('app.name').' digest')
            ->line($count.' new '.str('post')->plural($count).':');

        foreach ($this->posts as $post) {
            $preview = $post->preview(80) ?? 'New post';
            $message->line('**'.$post->user->name.'**: '.$preview.' — '.route('posts.show', $post));
        }

        return $this->appendUnsubscribeFooter($message);
    }
}
