<?php

namespace App\Console\Commands;

use App\Models\Invite;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('invite:make {--email= : Lock the invite to a specific email} {--days=14 : Days until expiry, 0 for no expiry}')]
#[Description('Mint an invite link for registration')]
class MakeInvite extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');

        $invite = Invite::mint(
            email: $this->option('email'),
            expiresInDays: $days > 0 ? $days : null,
        );

        $url = url('/register?invite='.$invite->token);

        $this->info('Invite created.');
        $this->line('  Link:    '.$url);
        $this->line('  Email:   '.($invite->email ?: 'any'));
        $this->line('  Expires: '.($invite->expires_at?->toDayDateTimeString() ?: 'never'));

        return self::SUCCESS;
    }
}
