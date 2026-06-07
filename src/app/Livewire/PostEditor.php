<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Validation\ValidationException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PostEditor extends Component
{
    public Post $post;

    public bool $editing = false;
    public bool $showPreview = false;

    #[Validate('nullable|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:10000')]
    public string $body = '';

    #[Validate('nullable|string|max:255')]
    public string $youtube = '';

    #[Validate(['selectedCategories' => 'array', 'selectedCategories.*' => 'integer|exists:categories,id'])]
    public array $selectedCategories = [];

    #[Validate('nullable|string|max:255')]
    public string $tags = '';

    public function startEditing(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->title = $this->post->title ?? '';
        $this->body = $this->post->body ?? '';
        $this->youtube = $this->post->youtube_id
            ? 'https://www.youtube.com/watch?v='.$this->post->youtube_id
            : '';
        $this->selectedCategories = $this->post->categories->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        $this->tags = $this->post->tags->pluck('name')->implode(' ');
        $this->showPreview = false;
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->showPreview = false;
        $this->resetValidation();
    }

    public function togglePreview(): void
    {
        $this->showPreview = ! $this->showPreview;
    }

    public function renderedBody(): string
    {
        $env = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $env->addExtension(new CommonMarkCoreExtension());

        return (string) (new MarkdownConverter($env))->convert($this->body);
    }

    public function save(): void
    {
        abort_unless($this->post->canEdit(auth()->user()), 403);

        $this->validate();

        $youtubeId = Post::parseYoutubeId($this->youtube);

        if ($this->youtube && ! $youtubeId) {
            throw ValidationException::withMessages([
                'youtube' => __('That does not look like a valid YouTube URL.'),
            ]);
        }

        if (trim($this->body) === '' && ! $this->post->media_path && ! $youtubeId) {
            throw ValidationException::withMessages([
                'body' => __('Write something, add media, or paste a YouTube link.'),
            ]);
        }

        $this->post->update([
            'title' => trim($this->title) ?: null,
            'body' => trim($this->body) ?: null,
            'youtube_id' => $youtubeId,
        ]);

        $this->post->categories()->sync($this->selectedCategories);
        $this->post->tags()->sync(Tag::fromText($this->tags)->pluck('id'));

        $this->post->refresh();
        $this->editing = false;
        $this->showPreview = false;
    }

    public function render()
    {
        return view('livewire.post-editor', [
            'categories' => Category::orderBy('name')->get(),
            'tagHints' => Tag::orderBy('name')->pluck('name'),
        ]);
    }
}
