<?php

namespace Tests\Feature;

use App\Livewire\AdminCategories;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admins_cannot_reach_the_admin_panel(): void
    {
        $this->get(route('admin.categories'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.categories'))
            ->assertForbidden();
    }

    public function test_an_admin_can_reach_the_admin_panel(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.categories'))
            ->assertOk();
    }

    public function test_an_admin_can_create_a_category_with_an_auto_slug(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminCategories::class)
            ->set('name', 'Tabletop Games')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Tabletop Games', 'slug' => 'tabletop-games']);
    }

    public function test_category_names_must_be_unique(): void
    {
        Category::create(['name' => 'Reading']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminCategories::class)
            ->set('name', 'Reading')
            ->call('create')
            ->assertHasErrors('name');
    }

    public function test_an_admin_can_rename_and_delete_a_category(): void
    {
        $category = Category::create(['name' => 'Misc']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminCategories::class)
            ->call('edit', $category)
            ->set('editingName', 'Miscellaneous')
            ->call('update')
            ->assertHasNoErrors()
            ->call('delete', $category);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_non_admin_cannot_render_the_component(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminCategories::class)
            ->assertForbidden();
    }
}
