<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPosts extends Component
{
    use HandlesPostDeletion, WithPagination;

    public PostForm $form;

    public ?int $editingId = null;

    public bool $showPreview = false;

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function edit(Post $post): void
    {
        $this->authorize('admin');

        $this->form->setFromPost($post);
        $this->editingId = $post->id;
    }

    public function update(): void
    {
        $this->authorize('admin');

        $this->form->save(Post::findOrFail($this->editingId));

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->showPreview = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $this->authorize('admin');

        return view('livewire.admin-posts', [
            'posts' => Post::withFeedRelations()->latest()->paginate(25),
            'categories' => $this->editingId ? Category::orderBy('name')->get() : collect(),
        ]);
    }
}
