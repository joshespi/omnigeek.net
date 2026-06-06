<?php

namespace Tests\Feature;

use App\Livewire\TagIndex;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_posts_show_until_a_tag_is_selected(): void
    {
        $user = User::factory()->create();
        $rust = Tag::fromText('rust')->first();
        $user->posts()->create(['body' => 'rusty'])->tags()->attach($rust);

        Livewire::test(TagIndex::class)
            ->assertDontSee('rusty');
    }

    public function test_selecting_a_tag_filters_to_its_posts(): void
    {
        $user = User::factory()->create();
        $rust = Tag::fromText('rust')->first();
        $synth = Tag::fromText('synth')->first();

        $user->posts()->create(['body' => 'rusty post'])->tags()->attach($rust);
        $user->posts()->create(['body' => 'synthy post'])->tags()->attach($synth);

        Livewire::test(TagIndex::class)
            ->call('toggle', 'rust')
            ->assertSee('rusty post')
            ->assertDontSee('synthy post');
    }

    public function test_multiple_tags_match_any_not_all(): void
    {
        $user = User::factory()->create();
        $rust = Tag::fromText('rust')->first();
        $synth = Tag::fromText('synth')->first();

        $user->posts()->create(['body' => 'rusty post'])->tags()->attach($rust);
        $user->posts()->create(['body' => 'synthy post'])->tags()->attach($synth);

        Livewire::test(TagIndex::class)
            ->call('toggle', 'rust')
            ->call('toggle', 'synth')
            ->assertSee('rusty post')
            ->assertSee('synthy post');
    }

    public function test_toggling_a_selected_tag_off_deselects_it(): void
    {
        Livewire::test(TagIndex::class)
            ->call('toggle', 'rust')
            ->assertSet('selected', ['rust'])
            ->call('toggle', 'rust')
            ->assertSet('selected', []);
    }
}
