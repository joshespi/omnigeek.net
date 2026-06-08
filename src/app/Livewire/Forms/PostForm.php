<?php

namespace App\Livewire\Forms;

use App\Enums\Feed;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\Tag;
use App\Support\PostMediaHandler;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PostForm extends Form
{
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

    #[Validate('nullable|date')]
    public string $publishedAt = '';

    public bool $toMemes = false;

    public bool $nsfw = false;

    public function setFromPost(Post $post): void
    {
        $post->loadMissing('categories', 'tags');

        $this->title = $post->title ?? '';
        $this->body = $post->body ?? '';
        $this->youtube = $post->youtube_id ?? '';
        $this->selectedCategories = $post->categories->pluck('id')->map(intval(...))->all();
        $this->tags = $post->tags->pluck('name')->implode(' ');
        $this->publishedAt = $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '';
        $this->toMemes = $post->feed === Feed::Memes;
        $this->nsfw = $post->nsfw;
    }

    public function save(?Post $post = null, array $media = []): Post
    {
        $this->validate();

        $youtubeId = Post::parseYoutubeId($this->youtube);

        if ($this->youtube && ! $youtubeId) {
            throw ValidationException::withMessages([
                'form.youtube' => __('That does not look like a valid YouTube URL.'),
            ]);
        }

        $hasExistingMedia = $post && $post->media()->exists();

        if (trim($this->body) === '' && empty($media) && ! $hasExistingMedia && ! $youtubeId) {
            throw ValidationException::withMessages([
                'form.body' => __('Write something, add media, or paste a YouTube link.'),
            ]);
        }

        $attributes = [
            'title'      => trim($this->title) ?: null,
            'body'       => trim($this->body) ?: null,
            'youtube_id' => $youtubeId,
            'published_at' => $this->publishedAt ? \Carbon\Carbon::parse($this->publishedAt) : null,
            'feed'       => $this->toMemes ? Feed::Memes : Feed::Main,
            // NSFW only applies to memes — a main-feed post is never marked NSFW.
            'nsfw'       => $this->toMemes && $this->nsfw,
        ];

        $isNew = ! $post;

        $post = $post
            ? tap($post)->update($attributes)
            : auth()->user()->posts()->create($attributes);

        // Resolve base order once instead of a MAX() query per file.
        $sortOrder = (int) $post->media()->max('sort_order');

        foreach ($media as $file) {
            PostMediaHandler::attach($post, $file, ++$sortOrder);
        }

        $post->categories()->sync($this->selectedCategories);
        $post->tags()->sync(Tag::fromText($this->tags)->pluck('id'));

        if ($isNew) {
            ActivityLog::record('post.create', 'post', $post->id, $post->preview(80), ['feed' => $post->feed->value]);
        }

        return $post->refresh();
    }
}
