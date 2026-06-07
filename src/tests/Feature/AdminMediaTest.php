<?php

namespace Tests\Feature;

use App\Enums\DigestCadence;
use App\Livewire\AdminMedia;
use App\Models\User;
use App\Support\SiteMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admins_cannot_reach_admin_media(): void
    {
        $this->get(route('admin.media'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.media'))
            ->assertForbidden();
    }

    public function test_admin_can_reach_admin_media(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.media'))
            ->assertOk();
    }

    public function test_admin_can_upload_a_logo(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminMedia::class)
            ->set('logo', UploadedFile::fake()->image('logo.png', 800, 800))
            ->call('saveLogo')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists(SiteMedia::LOGO);
    }

    public function test_admin_can_upload_an_og_image(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminMedia::class)
            ->set('ogImage', UploadedFile::fake()->image('og.png', 1200, 630))
            ->call('saveOgImage')
            ->assertHasNoErrors();

        Storage::disk('public')->assertExists(SiteMedia::OG_DEFAULT);
    }

    public function test_admin_can_change_the_digest_cadence(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminMedia::class)
            ->set('digestCadence', DigestCadence::Monthly->value)
            ->call('saveDigestCadence')
            ->assertHasNoErrors();

        $this->assertSame(DigestCadence::Monthly, DigestCadence::current());
    }

    public function test_non_admin_cannot_use_admin_media_component(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminMedia::class)
            ->call('saveDigestCadence')
            ->assertForbidden();
    }
}
