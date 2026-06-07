<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\PostMedia;
use App\Support\PostMediaHandler;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostMediaEditor extends Component
{
    use WithFileUploads;

    public Post $post;

    public bool $editContext = false;

    #[Validate(['uploads' => 'array', 'uploads.*' => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov'])]
    public array $uploads = [];

    #[Computed]
    public function items(): Collection
    {
        return $this->post->media;
    }

    public function addMedia(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->validate();

        // Resolve the base order once instead of a MAX() query per file.
        $sortOrder = (int) $this->post->media()->max('sort_order');

        foreach ($this->uploads as $file) {
            PostMediaHandler::attach($this->post, $file, ++$sortOrder);
        }

        $this->reset('uploads');
        $this->post->refresh();
    }

    public function removeItem(int $id): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $item = PostMedia::findOrFail($id);
        abort_unless($item->post_id === $this->post->id, 403);

        $item->delete();
        $this->post->refresh();
    }

    public function render()
    {
        return view('livewire.post-media-editor');
    }
}
