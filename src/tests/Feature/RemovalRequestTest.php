<?php

namespace Tests\Feature;

use App\Livewire\AdminRemovalRequests;
use App\Livewire\RequestRemoval;
use App\Models\Post;
use App\Models\RemovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RemovalRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_open_the_form_and_it_is_not_indexed(): void
    {
        $this->get(route('removal-request'))
            ->assertOk()
            ->assertSee('Request a removal')
            ->assertSee('noindex', false);
    }

    public function test_a_post_page_links_to_the_form_for_that_post(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee(route('removal-request', ['post' => $post->id]), false);
    }

    public function test_a_request_is_stored_against_the_post_it_came_from(): void
    {
        $post = Post::factory()->create(['title' => 'Three Falls in Silverpine']);

        Livewire::withQueryParams(['post' => $post->id])
            ->test(RequestRemoval::class)
            ->assertSet('postId', $post->id)
            ->assertSee('Three Falls in Silverpine')
            ->set('kind', 'name_in_image')
            ->set('details', 'My character name is visible in the second screenshot.')
            ->set('name', 'Roztusk')
            ->set('email', 'player@example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('done', true);

        $request = RemovalRequest::firstOrFail();

        $this->assertSame($post->id, $request->post_id);
        $this->assertSame('name_in_image', $request->kind);
        $this->assertSame('Roztusk', $request->name);
        $this->assertSame('player@example.com', $request->email);
        $this->assertFalse($request->isResolved());
    }

    public function test_name_and_email_are_optional_but_details_are_not(): void
    {
        Livewire::test(RequestRemoval::class)
            ->set('details', '')
            ->call('save')
            ->assertHasErrors('details');

        Livewire::test(RequestRemoval::class)
            ->set('details', 'Please remove my name from the dungeon post.')
            ->call('save')
            ->assertHasNoErrors();

        $request = RemovalRequest::firstOrFail();

        $this->assertNull($request->post_id);
        $this->assertNull($request->name);
        $this->assertNull($request->email);
    }

    public function test_an_unknown_kind_is_rejected(): void
    {
        Livewire::test(RequestRemoval::class)
            ->set('kind', 'nonsense')
            ->set('details', 'Please remove my name from the dungeon post.')
            ->call('save')
            ->assertHasErrors('kind');

        $this->assertSame(0, RemovalRequest::count());
    }

    public function test_a_scheduled_post_cannot_be_attached_by_a_stranger(): void
    {
        $scheduled = Post::factory()->create(['published_at' => now()->addWeek()]);

        Livewire::withQueryParams(['post' => $scheduled->id])
            ->test(RequestRemoval::class)
            ->assertSet('postId', null);
    }

    public function test_a_filled_honeypot_stores_nothing_but_looks_successful(): void
    {
        Livewire::test(RequestRemoval::class)
            ->set('details', 'Buy cheap watches at my website today.')
            ->set('website', 'https://spam.example')
            ->call('save')
            ->assertSet('done', true);

        $this->assertSame(0, RemovalRequest::count());
    }

    public function test_requests_are_rate_limited_per_address(): void
    {
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(RequestRemoval::class)
                ->set('details', "Request number {$i} about my name.")
                ->call('save')
                ->assertHasNoErrors();
        }

        Livewire::test(RequestRemoval::class)
            ->set('details', 'One request too many about my name.')
            ->call('save')
            ->assertHasErrors('details');

        $this->assertSame(5, RemovalRequest::count());
    }

    public function test_only_admins_can_reach_the_queue(): void
    {
        $this->get(route('admin.removal-requests'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.removal-requests'))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.removal-requests'))
            ->assertOk();
    }

    public function test_an_admin_can_resolve_reopen_and_delete_a_request(): void
    {
        $request = RemovalRequest::create(['kind' => 'other', 'details' => 'Please take my quote out.']);

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminRemovalRequests::class)
            ->assertSee('Please take my quote out.')
            ->call('resolve', $request->id);

        $this->assertTrue($request->refresh()->isResolved());

        $component->call('reopen', $request->id);

        $this->assertFalse($request->refresh()->isResolved());

        $component->call('delete', $request->id);

        $this->assertDatabaseMissing('removal_requests', ['id' => $request->id]);
    }

    public function test_the_admin_home_shows_how_many_requests_are_open(): void
    {
        RemovalRequest::create(['kind' => 'other', 'details' => 'First open request.']);
        RemovalRequest::create(['kind' => 'other', 'details' => 'Second open request.']);
        RemovalRequest::create(['kind' => 'other', 'details' => 'Already handled.', 'resolved_at' => now()]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.home'))
            ->assertOk()
            ->assertSee('2 open');
    }

    public function test_a_non_admin_cannot_render_the_queue(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminRemovalRequests::class)
            ->assertForbidden();
    }
}
