<?php

namespace App\Livewire\Forms;

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

    #[Validate('nullable|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,webm,mov')]
    public $media = null;

    #[Validate(['selectedCategories' => 'array', 'selectedCategories.*' => 'integer|exists:categories,id'])]
    public array $selectedCategories = [];

    #[Validate('nullable|string|max:255')]
    public string $tags = '';

    public function setFromPost(Post $post): void
    {
        $post->loadMissing('categories', 'tags');

        $this->title = $post->title ?? '';
        $this->body = $post->body ?? '';
        $this->youtube = $post->youtube_id ?? '';
        $this->selectedCategories = $post->categories->pluck('id')->map(intval(...))->all();
        $this->tags = $post->tags->pluck('name')->implode(' ');
    }

    public function save(?Post $post = null): Post
    {
        $this->validate();

        $youtubeId = Post::parseYoutubeId($this->youtube);

        if ($this->youtube && ! $youtubeId) {
            throw ValidationException::withMessages([
                'form.youtube' => __('That does not look like a valid YouTube URL.'),
            ]);
        }

        $hasExistingMedia = (bool) $post?->media_path;

        if (trim($this->body) === '' && ! $this->media && ! $hasExistingMedia && ! $youtubeId) {
            throw ValidationException::withMessages([
                'form.body' => __('Write something, add media, or paste a YouTube link.'),
            ]);
        }

        $attributes = [
            'title' => trim($this->title) ?: null,
            'body' => trim($this->body) ?: null,
            'youtube_id' => $youtubeId,
        ];

        if ($this->media) {
            $stored = PostMediaHandler::store($this->media);
            $attributes['media_path'] = $stored['path'];
            $attributes['media_type'] = $stored['type'];
        }

        $post = $post
            ? tap($post)->update($attributes)
            : auth()->user()->posts()->create($attributes);

        $post->categories()->sync($this->selectedCategories);
        $post->tags()->sync(Tag::fromText($this->tags)->pluck('id'));

        return $post->refresh();
    }
}
