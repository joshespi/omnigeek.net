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

    #[Validate(['uploads' => 'array', 'uploads.*' => PostMediaHandler::UPLOAD_RULE])]
    public array $uploads = [];

    // Caption text being edited, keyed by media id.
    public array $captions = [];

    public function mount(): void
    {
        $this->loadCaptions();
    }

    protected function loadCaptions(): void
    {
        $this->captions = $this->post->media->mapWithKeys(fn (PostMedia $m) => [$m->id => $m->caption ?? ''])->all();
    }

    #[Computed]
    public function items(): Collection
    {
        return $this->post->media;
    }

    public function addMedia(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->validate();

        PostMediaHandler::attachMany($this->post, $this->uploads);

        $this->reset('uploads');
        $this->post->refresh();
        $this->loadCaptions();
    }

    public function saveCaption(int $id): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $item = PostMedia::findOrFail($id);
        abort_unless($item->post_id === $this->post->id, 403);

        $this->validate(['captions.'.$id => ['nullable', 'string', 'max:500']], [], ['captions.'.$id => 'caption']);

        $item->update(['caption' => trim($this->captions[$id] ?? '') ?: null]);

        $this->post->refresh();
        $this->loadCaptions();
    }

    public function removeItem(int $id): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $item = PostMedia::findOrFail($id);
        abort_unless($item->post_id === $this->post->id, 403);

        $item->delete();
        $this->post->refresh();
        $this->loadCaptions();
    }

    public function render()
    {
        return view('livewire.post-media-editor');
    }
}
