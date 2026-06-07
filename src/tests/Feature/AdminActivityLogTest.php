<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_non_admins_cannot_reach_the_activity_log(): void
    {
        $this->get(route('admin.activity'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.activity'))
            ->assertForbidden();
    }

    public function test_an_admin_sees_logged_entries(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        ActivityLog::record('post.delete', 'post', 1, 'A removed post');

        $this->get(route('admin.activity'))
            ->assertOk()
            ->assertSee('A removed post');
    }
}
