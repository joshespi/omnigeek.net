<?php

namespace App\Livewire;

use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPosts extends Component
{
    use HandlesPostDeletion, WithPagination;

    public ?int $editingId = null;
    public string $editingTitle = '';
    public string $editingBody = '';
    public string $editingTags = '';
    public array $editingCategories = [];

    public function edit(Post $post): void
    {
        $this->authorize('admin');

        $post->load('categories', 'tags');
        $this->editingId = $post->id;
        $this->editingTitle = $post->title ?? '';
        $this->editingBody = $post->body ?? '';
        $this->editingCategories = $post->categories->pluck('id')->toArray();
        $this->editingTags = $post->tags->pluck('name')->implode(' ');
    }

    public function update(): void
    {
        $this->authorize('admin');

        $post = Post::findOrFail($this->editingId);

        $this->validate([
            'editingTitle' => 'nullable|string|max:255',
            'editingBody' => 'nullable|string|max:10000',
            'editingCategories' => 'array',
            'editingCategories.*' => 'integer|exists:categories,id',
            'editingTags' => 'nullable|string|max:255',
        ]);

        $post->update([
            'title' => trim($this->editingTitle) ?: null,
            'body' => trim($this->editingBody) ?: null,
        ]);
        $post->categories()->sync($this->editingCategories);
        $post->tags()->sync(Tag::fromText($this->editingTags)->pluck('id'));

        $this->cancelEdit();
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingTitle', 'editingBody', 'editingCategories', 'editingTags');
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
