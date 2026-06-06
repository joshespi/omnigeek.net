<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmSubscription extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirm your '.config('app.name').' subscription')
            ->line('Tap below to confirm you want new-post notifications from '.config('app.name').'.')
            ->action('Confirm subscription', route('subscriptions.confirm', $this->subscription->token))
            ->line('If you did not request this, you can ignore this email.');
    }
}
