<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait AppendsUnsubscribeFooter
{
    protected function appendUnsubscribeFooter(MailMessage $message): MailMessage
    {
        return $message
            ->line('Stop these emails any time:')
            ->line(route('subscriptions.unsubscribe', $this->subscription->token));
    }
}
