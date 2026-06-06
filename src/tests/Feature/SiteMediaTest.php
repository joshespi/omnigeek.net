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

        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('whatever.png'));

        Storage::disk('public')->assertExists(SiteMedia::LOGO);
        $this->assertNotNull(SiteMedia::logoUrl());
    }

    public function test_store_replaces_any_existing_file(): void
    {
        Storage::fake('public');

        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('first.png'));
        SiteMedia::store(SiteMedia::LOGO, UploadedFile::fake()->image('second.png'));

        Storage::disk('public')->assertExists(SiteMedia::LOGO);
    }

    public function test_delete_removes_the_file_and_url_goes_null(): void
    {
        Storage::fake('public');
        SiteMedia::store(SiteMedia::OG_DEFAULT, UploadedFile::fake()->image('og.png'));

        SiteMedia::delete(SiteMedia::OG_DEFAULT);

        Storage::disk('public')->assertMissing(SiteMedia::OG_DEFAULT);
        $this->assertNull(SiteMedia::ogDefaultUrl());
    }
}
