<?php

namespace App\Livewire;

use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Tag;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class ComposePost extends Component
{
    use WithFileUploads;

    public PostForm $form;

    #[Validate('nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov')]
    public $media = null;

    public bool $showPreview = false;

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function save(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validateOnly('media');

        $post = $this->form->save(media: $this->media);

        NotifySubscribersOfNewPost::dispatch($post);

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
        ]);
    }
}
