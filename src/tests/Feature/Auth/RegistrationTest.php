<?php

namespace Tests\Feature\Auth;

use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_registration_is_blocked_without_a_valid_invite(): void
    {
        Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors('token');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_new_users_can_register_with_a_valid_invite(): void
    {
        $invite = Invite::mint();

        Volt::test('pages.auth.register')
            ->set('token', $invite->token)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertNotNull($invite->fresh()->used_at);
    }

    public function test_a_used_invite_cannot_be_reused(): void
    {
        $invite = Invite::mint();
        $invite->forceFill(['used_at' => now()])->save();

        Volt::test('pages.auth.register')
            ->set('token', $invite->token)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors('token');

        $this->assertGuest();
    }
}
