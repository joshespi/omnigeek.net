<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesInlineImageUpload;
use App\Livewire\Forms\PostForm;
use App\Models\Post;
use App\Support\PostFormOptions;
use Livewire\Component;
use Livewire\WithFileUploads;

class PostEditor extends Component
{
    use HandlesInlineImageUpload, WithFileUploads;

    public Post $post;

    public PostForm $form;

    public bool $editing = false;

    public bool $showPreview = false;

    public function startEditing(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->form->setFromPost($this->post);
        $this->showPreview = false;
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->showPreview = false;
        $this->form->reset();
        $this->resetValidation();
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    protected function authorizeInlineImageUpload(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);
    }

    public function save(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->form->save($this->post);

        $this->editing = false;
        $this->showPreview = false;
    }

    public function render()
    {
        // Only the inline editor needs the full category/tag lists, and only one post is
        // edited at a time — so a 15-post feed runs these queries once on open, not 15x on load.
        $options = $this->editing
            ? PostFormOptions::forRender()
            : ['categories' => collect(), 'tagHints' => collect(), 'seriesHints' => collect()];

        return view('livewire.post-editor', $options);
    }
}
