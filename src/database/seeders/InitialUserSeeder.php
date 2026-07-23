<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    public function run(): User
    {
        $password = config('admin.initial_user.password')
            ?? throw new \RuntimeException('Set INITIAL_USER_PASSWORD before seeding — there is no default.');

        return User::firstOrCreate(
            ['email' => config('admin.initial_user.email')],
            [
                'name' => config('admin.initial_user.name'),
                'bio' => 'Resident omnigeek. Builder of small useful things.',
                'is_admin' => true,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
