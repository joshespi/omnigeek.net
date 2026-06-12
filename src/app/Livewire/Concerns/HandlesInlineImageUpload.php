<?php

namespace App\Livewire\Concerns;

use App\Support\ImageProcessor;
use Illuminate\Support\Facades\Storage;

/**
 * Lets a markdown editor upload an image and get back a public URL to drop a
 * `![](url)` reference into the body. The host component must also use
 * Livewire\WithFileUploads. The file goes through the same re-encoding pipeline
 * as gallery media (ImageProcessor), so embedded payloads are stripped and the
 * stored extension is derived from the real MIME, not the filename.
 */
trait HandlesInlineImageUpload
{
    public $inlineImage = null;

    public function updatedInlineImage(): void
    {
        $this->validateOnly('inlineImage', [
            'inlineImage' => 'image|max:51200',
        ]);

        $path = ImageProcessor::compress($this->inlineImage, 'uploads', 1600);

        $this->dispatch('inline-image-uploaded', url: Storage::disk('public')->url($path));

        $this->reset('inlineImage');
    }
}
