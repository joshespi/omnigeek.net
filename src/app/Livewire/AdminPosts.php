<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Livewire\Forms\PostForm;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use App\Support\PostMediaHandler;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPosts extends Component
{
    use HandlesPostDeletion, WithPagination;

    public function deletePost(Post $post): void
    {
        abort_unless($post->canDelete(auth()->user()), 403);

        $label = $post->preview(80);
        $id = $post->id;

        PostMediaHandler::delete($post->media_path);
        $post->delete();

        ActivityLog::record('post.delete', 'post', $id, $label);

        $this->afterDelete();
    }

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

        $post = Post::findOrFail($this->editingId);
        $this->form->save($post);

        ActivityLog::record('post.update', 'post', $post->id, $post->preview(80));

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
