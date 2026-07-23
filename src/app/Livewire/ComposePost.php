<?php

namespace App\Livewire;

use App\Enums\Feed;
use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\Concerns\HandlesInlineImageUpload;
use App\Livewire\Forms\PostForm;
use App\Support\PostFormOptions;
use App\Support\PostMediaHandler;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

class ComposePost extends Component
{
    use HandlesInlineImageUpload, WithFileUploads;

    public PostForm $form;

    #[Validate(['media' => 'array', 'media.*' => PostMediaHandler::UPLOAD_RULE])]
    public array $media = [];

    public bool $showPreview = false;

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function save(): void
    {
        abort_unless(auth()->check(), 403);

        $this->validate();

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
        return view('livewire.compose-post', PostFormOptions::forRender());
    }
}
