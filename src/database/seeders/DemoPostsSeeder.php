<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DemoPostsSeeder extends Seeder
{
    public function run(): void
    {
        $image = $this->copyAsset('demo-image.png');
        $video = $this->copyAsset('demo-video.mp4');

        $primary = User::firstOrCreate(
            ['email' => config('demo.user.email')],
            [
                'name' => config('demo.user.name'),
                'bio' => 'Resident omnigeek. Builder of small useful things.',
                'is_admin' => true,
                'password' => Hash::make(config('demo.user.password')),
                'email_verified_at' => now(),
            ],
        );

        $this->seedPosts($primary, [
            ['body' => 'Plain text post. No frills, just words.'],
            ['body' => 'Text plus an image below.', 'media_path' => $image, 'media_type' => 'image'],
            ['media_path' => $image, 'media_type' => 'image'],
            ['body' => 'Text plus a video.', 'media_path' => $video, 'media_type' => 'video'],
            ['media_path' => $video, 'media_type' => 'video'],
            ['body' => 'Never gonna give you up.', 'youtube_id' => 'dQw4w9WgXcQ'],
            ['youtube_id' => 'dQw4w9WgXcQ'],
        ]);

        $geeks = [
            [
                'name' => 'Ada Pixel',
                'email' => 'ada@omnigeek.test',
                'bio' => 'Retro hardware tinkerer. I solder things that already worked fine.',
                'posts' => [
                    ['body' => 'Recapped a 1987 motherboard tonight. It boots. I weep.'],
                    ['body' => 'CRT glow > OLED. Fight me.', 'media_path' => $image, 'media_type' => 'image'],
                    ['body' => 'Speedrun of my favorite platformer.', 'youtube_id' => 'dQw4w9WgXcQ'],
                ],
            ],
            [
                'name' => 'Rex Kernel',
                'email' => 'rex@omnigeek.test',
                'bio' => 'Self-hosting everything I can. The cloud is just someone else\'s computer.',
                'posts' => [
                    ['body' => 'Migrated off the last SaaS I was paying for. Freedom.'],
                    ['body' => 'New rack day.', 'media_path' => $image, 'media_type' => 'image'],
                    ['body' => 'Clip from my homelab tour.', 'media_path' => $video, 'media_type' => 'video'],
                ],
            ],
            [
                'name' => 'Mei Render',
                'email' => 'mei@omnigeek.test',
                'bio' => 'Indie game dev. Currently making a roguelike about a cat who runs a library.',
                'posts' => [
                    ['body' => 'Shipped a new build. Save bug finally squashed.'],
                    ['body' => 'Concept art for the next boss.', 'media_path' => $image, 'media_type' => 'image'],
                    ['body' => 'Devlog #12.', 'youtube_id' => 'dQw4w9WgXcQ'],
                ],
            ],
            [
                'name' => 'Otto Synth',
                'email' => 'otto@omnigeek.test',
                'bio' => 'Modular synth hoarder and Rust enjoyer. Yes, I rewrote it in Rust.',
                'posts' => [
                    ['body' => 'Patched a generative ambient drone. Slept like a baby.'],
                    ['body' => 'New oscillator module.', 'media_path' => $image, 'media_type' => 'image'],
                    ['body' => 'Jam session recording.', 'media_path' => $video, 'media_type' => 'video'],
                ],
            ],
        ];

        foreach ($geeks as $geek) {
            $user = User::firstOrCreate(
                ['email' => $geek['email']],
                [
                    'name' => $geek['name'],
                    'bio' => $geek['bio'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );

            $this->seedPosts($user, $geek['posts']);
        }
    }

    private function seedPosts(User $user, array $posts): void
    {
        if ($user->posts()->exists()) {
            return;
        }

        foreach ($posts as $attrs) {
            $user->posts()->create($attrs);
        }
    }

    private function copyAsset(string $file): string
    {
        $target = 'uploads/'.$file;
        Storage::disk('public')->put($target, file_get_contents(__DIR__.'/assets/'.$file));

        return $target;
    }
}
