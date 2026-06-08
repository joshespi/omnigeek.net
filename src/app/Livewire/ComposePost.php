<?php

namespace App\Livewire;

use App\Enums\Feed;
use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Series;
use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class ComposePost extends Component
{
    use WithFileUploads;

    public PostForm $form;

    #[Validate(['media' => 'array', 'media.*' => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov'])]
    public array $media = [];

    public bool $showPreview = false;

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function save(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate(['media' => 'array', 'media.*' => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov']);

        $post = $this->form->save(media: $this->media);

        // Memes are low-signal junk — never email subscribers about them.
        if ($post->feed === Feed::Main) {
            NotifySubscribersOfNewPost::dispatch($post);
        }

        $this->reset('media');
        $this->form->reset();
        $this->showPreview = false;

        $this->dispatch('post-created');
        $this->dispatch('close-compose');
    }

    public function render()
    {
        return view('livewire.compose-post', [
            'categories' => Category::orderBy('name')->get(),
            'tagHints' => Tag::orderBy('name')->pluck('name'),
            'seriesHints' => Series::orderBy('name')->pluck('name'),
        ]);
    }
}
