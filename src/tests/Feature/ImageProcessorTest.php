<?php

namespace Tests\Feature;

use App\Support\ImageProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Tests\TestCase;

class ImageProcessorTest extends TestCase
{
    private function storedWidth(string $path): int
    {
        return (new ImageManager(Driver::class))
            ->decode(Storage::disk('public')->get($path))
            ->width();
    }

    public function test_it_downscales_an_oversized_image_to_the_max_width(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('big.jpg', 2400, 1800);

        $path = ImageProcessor::compress($file, 'uploads', 1600);

        $this->assertSame(1600, $this->storedWidth($path));
    }

    public function test_it_leaves_a_small_image_at_its_own_size(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('small.jpg', 400, 300);

        $path = ImageProcessor::compress($file, 'uploads', 1600);

        $this->assertSame(400, $this->storedWidth($path));
    }

    public function test_png_stays_png_and_jpeg_stays_jpg(): void
    {
        Storage::fake('public');

        $png = ImageProcessor::compress(UploadedFile::fake()->image('a.png', 100, 100), 'uploads', 512);
        $jpg = ImageProcessor::compress(UploadedFile::fake()->image('b.jpg', 100, 100), 'uploads', 512);

        $this->assertStringEndsWith('.png', $png);
        $this->assertStringEndsWith('.jpg', $jpg);
    }

    public function test_compress_to_writes_the_exact_path(): void
    {
        Storage::fake('public');

        ImageProcessor::compressTo(UploadedFile::fake()->image('x.png', 1000, 600), 'site/og-default.png', 1200);

        Storage::disk('public')->assertExists('site/og-default.png');
    }
}
