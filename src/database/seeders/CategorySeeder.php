<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // DatabaseSeeder disables model events (WithoutModelEvents), so the HasSlug
        // saving hook won't fire here — set the slug explicitly.
        foreach (['Video Games', 'Reading', 'Movies/TV', 'Misc'] as $name) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
