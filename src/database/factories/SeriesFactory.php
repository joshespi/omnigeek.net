<?php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Series>
 */
class SeriesFactory extends Factory
{
    protected $model = Series::class;

    public function definition(): array
    {
        // slug is set by HasSlug on save; name drives it.
        return [
            'name' => fake()->unique()->catchPhrase(),
        ];
    }
}
