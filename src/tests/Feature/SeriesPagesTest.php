<?php

namespace Tests\Feature;

use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\AdminSeries;
use App\Livewire\Subscribe;
use App\Models\Post;
use App\Models\Series;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\NewPostPublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class SeriesPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_series_index_lists_only_series_with_published_posts(): void
    {
        $live = Series::factory()->create(['name' => 'Journal of Pally Espi', 'description' => 'A **dead** paladin keeps a record.']);
        Post::factory()->inSeries($live, 1)->create();
        Post::factory()->inSeries($live, 2)->create();
        Post::factory()->inSeries($live, 3)->create(['published_at' => now()->addWeek()]);

        $scheduledOnly = Series::factory()->create(['name' => 'Not Out Yet']);
        Post::factory()->inSeries($scheduledOnly, 1)->create(['published_at' => now()->addWeek()]);

        Series::factory()->create(['name' => 'Empty Shell']);

        $this->get(route('series.index'))
            ->assertOk()
            ->assertSee('Journal of Pally Espi')
            ->assertSee('2 parts')
            // The introduction is excerpted as plain text, without Markdown syntax.
            ->assertSee('A dead paladin keeps a record.')
            ->assertDontSee('**dead**')
            ->assertDontSee('Not Out Yet')
            ->assertDontSee('Empty Shell');
    }

    public function test_series_page_shows_its_introduction_and_where_to_start(): void
    {
        $series = Series::factory()->create(['description' => 'He died in the **Third War**.']);
        Post::factory()->inSeries($series, 2)->create(['title' => 'Second']);
        $first = Post::factory()->inSeries($series, 1)->create(['title' => 'First']);

        $this->get(route('series.show', $series))
            ->assertOk()
            ->assertSee('<strong>Third War</strong>', false)
            ->assertSee('Start reading')
            ->assertSee(route('posts.show', $first), false)
            ->assertSee(route('subscribe', ['series' => $series->slug]), false);
    }

    public function test_a_series_with_nothing_published_offers_no_start_link(): void
    {
        $series = Series::factory()->create();
        Post::factory()->inSeries($series, 1)->create(['published_at' => now()->addWeek()]);

        $this->get(route('series.show', $series))
            ->assertOk()
            ->assertDontSee('Start reading');
    }

    public function test_only_admins_can_reach_series_admin(): void
    {
        $this->get(route('admin.series'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.series'))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.series'))
            ->assertOk();
    }

    public function test_an_admin_can_rename_a_series_and_write_its_introduction(): void
    {
        $series = Series::factory()->create(['name' => 'Old Name']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminSeries::class)
            ->call('edit', $series->id)
            ->assertSet('editingName', 'Old Name')
            ->set('editingName', 'New Name')
            ->set('editingDescription', 'What this series is about.')
            ->call('update')
            ->assertHasNoErrors()
            ->assertSet('editingId', null);

        $series->refresh();

        $this->assertSame('New Name', $series->name);
        // The slug stays put so links and subscriptions keep working.
        $this->assertSame('old-name', $series->slug);
        $this->assertSame('What this series is about.', $series->description);
    }

    public function test_renaming_a_series_onto_another_series_is_refused(): void
    {
        Series::factory()->create(['name' => 'Taken Name']);
        $series = Series::factory()->create(['name' => 'Mine']);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminSeries::class)
            ->call('edit', $series->id)
            ->set('editingName', 'taken name')
            ->call('update')
            ->assertHasErrors('editingName');

        $this->assertSame('Mine', $series->refresh()->name);
    }

    public function test_deleting_a_series_keeps_its_posts(): void
    {
        $series = Series::factory()->create();
        $post = Post::factory()->inSeries($series, 1)->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminSeries::class)
            ->call('delete', $series->id);

        $this->assertDatabaseMissing('series', ['id' => $series->id]);
        $this->assertNull($post->refresh()->series_id);
    }

    public function test_a_non_admin_cannot_render_series_admin(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminSeries::class)
            ->assertForbidden();
    }

    public function test_a_follow_link_preselects_the_series_on_the_subscribe_form(): void
    {
        Notification::fake();

        $series = Series::factory()->create(['name' => 'Journal of Pally Espi']);
        Post::factory()->inSeries($series, 1)->create();

        Livewire::withQueryParams(['series' => $series->slug])
            ->test(Subscribe::class)
            ->assertSet('series', [$series->slug])
            ->set('email', 'reader@example.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(
            ['series' => [$series->slug]],
            Subscription::where('email', 'reader@example.com')->firstOrFail()->filters,
        );
    }

    public function test_an_unknown_series_in_the_follow_link_is_ignored(): void
    {
        Livewire::withQueryParams(['series' => 'no-such-series'])
            ->test(Subscribe::class)
            ->assertSet('series', []);
    }

    public function test_series_followers_only_hear_about_that_series(): void
    {
        Notification::fake();

        $journal = Series::factory()->create();
        $other = Series::factory()->create();

        Subscription::create(['email' => 'journal@example.com', 'confirmed_at' => now(), 'filters' => ['series' => [$journal->slug]]]);
        Subscription::create(['email' => 'other@example.com', 'confirmed_at' => now(), 'filters' => ['series' => [$other->slug]]]);
        Subscription::create(['email' => 'everything@example.com', 'confirmed_at' => now()]);

        $entry = Post::factory()->inSeries($journal, 1)->create();

        (new NotifySubscribersOfNewPost($entry))->handle();

        // The journal follower and the no-filter subscriber; not the other series' follower.
        Notification::assertSentOnDemandTimes(NewPostPublished::class, 2);

        Notification::fake();

        // A post in no series reaches only the no-filter subscriber.
        (new NotifySubscribersOfNewPost(Post::factory()->create()))->handle();

        Notification::assertSentOnDemandTimes(NewPostPublished::class, 1);
    }
}
