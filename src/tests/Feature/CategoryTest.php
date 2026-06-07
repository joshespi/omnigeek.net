<?php

namespace Tests\Feature;

use App\Livewire\CategoryFeed;
use App\Livewire\ComposePost;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_categories_with_slugs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('categories', ['name' => 'Video Games', 'slug' => 'video-games']);
        $this->assertSame(0, Category::whereNull('slug')->count());
    }

    public function test_a_poster_can_attach_categories_to_a_post(): void
    {
        $user = User::factory()->create();
        $games = Category::create(['name' => 'Video Games']);
        $misc = Category::create(['name' => 'Misc']);

        Livewire::actingAs($user)
            ->test(ComposePost::class)
            ->set('form.body', 'Categorised post')
            ->set('form.selectedCategories', [$games->id, $misc->id])
            ->call('save')
            ->assertHasNoErrors();

        $post = $user->posts()->first();
        $this->assertEqualsCanonicalizing([$games->id, $misc->id], $post->categories->pluck('id')->all());
    }

    public function test_an_unknown_category_id_is_rejected(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ComposePost::class)
            ->set('form.body', 'Bad category')
            ->set('form.selectedCategories', [9999])
            ->call('save')
            ->assertHasErrors('form.selectedCategories.0');
    }

    public function test_category_feed_shows_only_that_categorys_posts(): void
    {
        $user = User::factory()->create();
        $games = Category::create(['name' => 'Video Games']);

        $inCategory = $user->posts()->create(['body' => 'A games post']);
        $inCategory->categories()->attach($games);
        $user->posts()->create(['body' => 'An uncategorised post']);

        Livewire::test(CategoryFeed::class, ['category' => $games])
            ->assertSee('A games post')
            ->assertDontSee('An uncategorised post');
    }

    public function test_category_is_resolved_by_slug(): void
    {
        $category = Category::create(['name' => 'Movies/TV']);

        $this->get(route('categories.show', $category))->assertOk();
        $this->assertSame('moviestv', $category->slug);
    }
}
