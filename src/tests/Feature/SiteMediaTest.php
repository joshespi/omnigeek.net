<?php

namespace Tests\Feature;

use App\Support\SiteMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteMediaTest extends TestCase
{
    public function test_store_lands_the_file_at_the_fixed_path(): void
    {
        Storage::fake('public');

        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('whatever.png', 800, 800), 512);

        Storage::disk('public')->assertExists(SiteMedia::LOGO);
        $this->assertNotNull(SiteMedia::logoUrl());
    }

    public function test_url_carries_a_version_and_mtime_cache_bust_token(): void
    {
        config(['app.version' => '9.9.9']);
        Storage::fake('public');
        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('whatever.png', 800, 800), 512);

        $mtime = Storage::disk('public')->lastModified(SiteMedia::LOGO);

        $this->assertStringContainsString('?v=9.9.9-'.$mtime, SiteMedia::logoUrl());
    }

    public function test_store_replaces_any_existing_file(): void
    {
        Storage::fake('public');

        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('first.png', 800, 800), 512);
        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('second.png', 800, 800), 512);

        Storage::disk('public')->assertExists(SiteMedia::LOGO);
    }

    public function test_delete_removes_the_file_and_url_goes_null(): void
    {
        Storage::fake('public');
        SiteMedia::store(SiteMedia::OG_DEFAULT, UploadedFile::fake()->image('og.png', 1200, 630), 1200);

        SiteMedia::delete(SiteMedia::OG_DEFAULT);

        Storage::disk('public')->assertMissing(SiteMedia::OG_DEFAULT);
        $this->assertNull(SiteMedia::ogDefaultUrl());
    }
}
