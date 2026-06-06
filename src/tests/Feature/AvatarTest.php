<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_upload_an_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('profile.update-avatar-form')
            ->set('avatar', UploadedFile::fake()->image('me.jpg'))
            ->call('updateAvatar')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_uploading_a_new_avatar_replaces_the_old_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $component = Volt::actingAs($user)->test('profile.update-avatar-form');

        $component->set('avatar', UploadedFile::fake()->image('first.jpg'))->call('updateAvatar');
        $first = $user->refresh()->avatar_path;

        $component->set('avatar', UploadedFile::fake()->image('second.jpg'))->call('updateAvatar');
        $second = $user->refresh()->avatar_path;

        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_a_user_can_remove_their_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('profile.update-avatar-form')
            ->set('avatar', UploadedFile::fake()->image('me.jpg'))
            ->call('updateAvatar')
            ->call('removeAvatar');

        $this->assertNull($user->refresh()->avatar_path);
    }

    public function test_a_non_image_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        Volt::actingAs($user)
            ->test('profile.update-avatar-form')
            ->set('avatar', UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'))
            ->call('updateAvatar')
            ->assertHasErrors('avatar');
    }

    public function test_avatar_url_is_null_without_an_avatar(): void
    {
        $this->assertNull(User::factory()->create()->avatar_url);
        $this->assertNotEmpty(User::factory()->make(['name' => 'Jane Doe'])->initials());
    }
}
