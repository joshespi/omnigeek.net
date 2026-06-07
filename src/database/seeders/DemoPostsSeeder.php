<?php

namespace Database\Seeders;

use App\Models\Tag;
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

        // The initial admin user is created by InitialUserSeeder; attach demo posts to it.
        $primary = (new InitialUserSeeder)->run();

        $this->seedPosts($primary, [
            ['body' => 'Plain text post. No frills, just words.', 'tags' => 'meta intro'],
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
                    ['body' => 'Recapped a 1987 motherboard tonight. It boots. I weep.', 'tags' => 'retro hardware soldering'],
                    ['body' => 'CRT glow > OLED. Fight me.', 'media_path' => $image, 'media_type' => 'image', 'tags' => 'retro'],
                    ['body' => 'Speedrun of my favorite platformer.', 'youtube_id' => 'dQw4w9WgXcQ', 'tags' => 'gaming'],
                ],
            ],
            [
                'name' => 'Rex Kernel',
                'email' => 'rex@omnigeek.test',
                'bio' => 'Self-hosting everything I can. The cloud is just someone else\'s computer.',
                'posts' => [
                    ['body' => 'Migrated off the last SaaS I was paying for. Freedom.', 'tags' => 'selfhosting homelab'],
                    ['body' => 'New rack day.', 'media_path' => $image, 'media_type' => 'image', 'tags' => 'homelab hardware'],
                    ['body' => 'Clip from my homelab tour.', 'media_path' => $video, 'media_type' => 'video', 'tags' => 'homelab'],
                ],
            ],
            [
                'name' => 'Mei Render',
                'email' => 'mei@omnigeek.test',
                'bio' => 'Indie game dev. Currently making a roguelike about a cat who runs a library.',
                'posts' => [
                    ['body' => 'Shipped a new build. Save bug finally squashed.', 'tags' => 'gamedev'],
                    ['body' => 'Concept art for the next boss.', 'media_path' => $image, 'media_type' => 'image', 'tags' => 'gamedev art'],
                    ['body' => 'Devlog #12.', 'youtube_id' => 'dQw4w9WgXcQ', 'tags' => 'gamedev gaming'],
                ],
            ],
            [
                'name' => 'Otto Synth',
                'email' => 'otto@omnigeek.test',
                'bio' => 'Modular synth hoarder and Rust enjoyer. Yes, I rewrote it in Rust.',
                'posts' => [
                    ['body' => 'Patched a generative ambient drone. Slept like a baby.', 'tags' => 'synth music'],
                    ['body' => 'New oscillator module.', 'media_path' => $image, 'media_type' => 'image', 'tags' => 'synth hardware'],
                    ['body' => 'Jam session recording.', 'media_path' => $video, 'media_type' => 'video', 'tags' => 'synth music rust'],
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
            $tags      = $attrs['tags'] ?? null;
            $mediaPath = $attrs['media_path'] ?? null;
            $mediaType = $attrs['media_type'] ?? null;
            unset($attrs['tags'], $attrs['media_path'], $attrs['media_type']);

            $post = $user->posts()->create($attrs);

            if ($mediaPath) {
                $post->media()->create([
                    'path'       => $mediaPath,
                    'type'       => $mediaType ?? 'image',
                    'sort_order' => 0,
                ]);
            }

            if ($tags) {
                $post->tags()->sync(Tag::fromText($tags)->pluck('id'));
            }
        }
    }

    private function copyAsset(string $file): string
    {
        $target = 'uploads/'.$file;
        Storage::disk('public')->put($target, file_get_contents(__DIR__.'/assets/'.$file));

        return $target;
    }
}
