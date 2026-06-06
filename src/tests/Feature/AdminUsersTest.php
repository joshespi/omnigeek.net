<?php

namespace Tests\Feature;

use App\Livewire\AdminUsers;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admins_cannot_reach_admin_users(): void
    {
        $this->get(route('admin.users'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.users'))
            ->assertForbidden();
    }

    public function test_admin_can_reach_admin_users(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.users'))
            ->assertOk();
    }

    public function test_admin_can_generate_invite_link(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->set('inviteEmail', 'friend@example.com')
            ->set('inviteDays', 7)
            ->call('sendInvite')
            ->assertHasNoErrors()
            ->assertSet('inviteLink', fn ($link) => str_contains($link, '/register?invite='));

        $this->assertDatabaseHas('invites', ['email' => 'friend@example.com']);
    }

    public function test_admin_can_generate_open_invite(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(AdminUsers::class)
            ->set('inviteEmail', '')
            ->call('sendInvite')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invites', ['email' => null]);
    }

    public function test_admin_can_edit_user_name_and_email(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('edit', $user)
            ->set('editingName', 'New Name')
            ->set('editingEmail', 'new@example.com')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'email' => 'new@example.com']);
    }

    public function test_admin_can_toggle_another_users_admin_status(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('toggleAdmin', $user);

        $this->assertTrue($user->fresh()->is_admin);

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('toggleAdmin', $user);

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_admin_cannot_toggle_their_own_admin_status(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(AdminUsers::class)
            ->call('toggleAdmin', $admin)
            ->assertForbidden();
    }

    public function test_non_admin_cannot_use_admin_users_component(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(AdminUsers::class)
            ->assertForbidden();
    }
}
