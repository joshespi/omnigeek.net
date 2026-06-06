<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\InitialUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialUserSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_admin_user_with_working_credentials_from_config(): void
    {
        config([
            'admin.initial_user.name' => 'Boss',
            'admin.initial_user.email' => 'boss@example.com',
            'admin.initial_user.password' => 's3cret',
        ]);

        $this->seed(InitialUserSeeder::class);

        $user = User::where('email', 'boss@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(Hash::check('s3cret', $user->password));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_it_is_idempotent(): void
    {
        $this->seed(InitialUserSeeder::class);
        $this->seed(InitialUserSeeder::class);

        $this->assertSame(1, User::where('email', config('admin.initial_user.email'))->count());
    }
}
