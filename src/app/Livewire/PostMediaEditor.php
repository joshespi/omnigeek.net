<?php

namespace App\Livewire;

use App\Models\Post;
use App\Support\ImageProcessor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostMediaEditor extends Component
{
    use WithFileUploads;

    public Post $post;

    public bool $editing = false;

    #[Validate('required|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov')]
    public $replacement = null;

    public function replaceMedia(): void
    {
        abort_unless($this->post->canDelete(auth()->user()), 403);

        $this->validate();

        if ($this->post->media_path) {
            Storage::disk('public')->delete($this->post->media_path);
        }

        $isVideo = str_starts_with($this->replacement->getMimeType(), 'video/');
        $this->post->update([
            'media_path' => $isVideo
                ? $this->replacement->store('uploads', 'public')
                : ImageProcessor::compress($this->replacement, 'uploads', 1600),
            'media_type' => $isVideo ? 'video' : 'image',
        ]);

        $this->editing = false;
        $this->reset('replacement');
    }

    public function removeMedia(): void
    {
        abort_unless($this->post->canDelete(auth()->user()), 403);

        if ($this->post->media_path) {
            Storage::disk('public')->delete($this->post->media_path);
        }

        $this->post->update(['media_path' => null, 'media_type' => null]);
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.post-media-editor');
    }
}
