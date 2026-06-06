<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invite extends Model
{
    protected $fillable = ['token', 'email', 'created_by', 'used_by', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function mint(?string $email = null, ?int $createdBy = null, ?int $expiresInDays = 14): self
    {
        return static::create([
            'token' => Str::random(40),
            'email' => $email,
            'created_by' => $createdBy,
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
    }

    public function isUsable(): bool
    {
        if ($this->used_at) {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }
}
