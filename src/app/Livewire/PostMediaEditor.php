<?php

namespace App\Livewire;

use App\Models\Post;
use App\Support\PostMediaHandler;
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

        PostMediaHandler::delete($this->post->media_path);

        $stored = PostMediaHandler::store($this->replacement);
        $this->post->update(['media_path' => $stored['path'], 'media_type' => $stored['type']]);

        $this->editing = false;
        $this->reset('replacement');
    }

    public function removeMedia(): void
    {
        abort_unless($this->post->canDelete(auth()->user()), 403);

        PostMediaHandler::delete($this->post->media_path);

        $this->post->update(['media_path' => null, 'media_type' => null]);
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.post-media-editor');
    }
}
