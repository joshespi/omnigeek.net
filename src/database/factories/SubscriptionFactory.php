<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'token' => \Illuminate\Support\Str::random(48),
            'confirmed_at' => null,
            'channel' => 'email',
            'frequency' => 'instant',
            'filters' => null,
            'last_notified_at' => null,
        ];
    }
}
