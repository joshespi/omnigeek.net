<?php

namespace Database\Factories;

use App\Enums\Feed;
use App\Models\Post;
use App\Models\Series;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
            'youtube_id' => null,
            // feed defaults to 'main' via the DB default + model $attributes.
        ];
    }

    public function memes(): static
    {
        return $this->state(['feed' => Feed::Memes->value]);
    }

    public function nsfw(): static
    {
        // NSFW implies memes — compose memes() so the feed assignment lives in one place.
        return $this->memes()->state(['nsfw' => true]);
    }

    public function inSeries(Series $series, ?int $part = null): static
    {
        return $this->state(['series_id' => $series->id, 'series_part' => $part]);
    }
}
