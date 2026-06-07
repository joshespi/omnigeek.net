<?php

namespace App\Livewire;

use App\Jobs\NotifySubscribersOfNewPost;
use App\Livewire\Concerns\HandlesPostDeletion;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Support\ImageProcessor;
use Illuminate\Validation\ValidationException;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Feed extends Component
{
    use HandlesPostDeletion;
    use WithFileUploads;
    use WithPagination;

    #[Validate('nullable|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:10000')]
    public string $body = '';

    #[Validate('nullable|string|max:255')]
    public string $youtube = '';

    #[Validate('nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov')]
    public $media = null;

    #[Validate(['selectedCategories' => 'array', 'selectedCategories.*' => 'integer|exists:categories,id'])]
    public array $selectedCategories = [];

    #[Validate('nullable|string|max:255')]
    public string $tags = '';

    public bool $showPreview = false;

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
        abort_unless(auth()->check(), 403);

        $this->validate();

        $youtubeId = Post::parseYoutubeId($this->youtube);

        if ($this->youtube && ! $youtubeId) {
            throw ValidationException::withMessages([
                'youtube' => __('That does not look like a valid YouTube URL.'),
            ]);
        }

        if (trim($this->body) === '' && ! $this->media && ! $youtubeId) {
            throw ValidationException::withMessages([
                'body' => __('Write something, add media, or paste a YouTube link.'),
            ]);
        }

        $mediaPath = null;
        $mediaType = null;
        if ($this->media) {
            $isVideo = str_starts_with($this->media->getMimeType(), 'video/');
            $mediaType = $isVideo ? 'video' : 'image';
            $mediaPath = $isVideo
                ? $this->media->store('uploads', 'public')
                : ImageProcessor::compress($this->media, 'uploads', 1600);
        }

        $post = auth()->user()->posts()->create([
            'title' => trim($this->title) ?: null,
            'body' => trim($this->body) ?: null,
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'youtube_id' => $youtubeId,
        ]);

        $post->categories()->sync($this->selectedCategories);
        $post->tags()->sync(Tag::fromText($this->tags)->pluck('id'));

        NotifySubscribersOfNewPost::dispatch($post);

        $this->reset('title', 'body', 'youtube', 'media', 'selectedCategories', 'tags');
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.feed', [
            'posts' => Post::withFeedRelations()->latest()->paginate(15),
            'categories' => Category::orderBy('name')->get(),
            'tagHints' => Tag::orderBy('name')->pluck('name'),
        ]);
    }
}
