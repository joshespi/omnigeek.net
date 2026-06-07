<?php

namespace App\Livewire;

use App\Enums\Feed;
use App\Livewire\Concerns\HandlesPostModeration;
use App\Livewire\Forms\PostForm;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPosts extends Component
{
    use HandlesPostModeration, WithPagination;

    // 'all' | 'main' | 'memes' — admin feed filter.
    #[Url]
    public string $feedFilter = 'all';

    public function updatingFeedFilter(): void
    {
        $this->resetPage();
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

        $post = Post::anyFeed()->findOrFail($this->editingId);
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

        $query = Post::withFeedRelations()->latestForFeed();

        // 'all' (or any unknown value) → every feed; a valid enum value → just that feed.
        $query = ($feed = Feed::tryFrom($this->feedFilter))
            ? $query->ofFeed($feed)
            : $query->anyFeed();

        return view('livewire.admin-posts', [
            'posts' => $query->paginate(25),
            'categories' => $this->editingId ? Category::orderBy('name')->get() : collect(),
        ]);
    }
}
