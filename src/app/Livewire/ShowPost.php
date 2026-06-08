<?php

namespace App\Livewire;

use App\Enums\Feed;
use App\Livewire\Concerns\HandlesPostModeration;
use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    use HandlesPostModeration;

    public Post $post;

    public function mount(Post $post): void
    {
        $this->post = $post->load('user', 'categories', 'tags', 'media', 'series');
        $post->increment('view_count');
    }

    // Gather the post's series siblings (published only) plus its prev/next neighbours,
    // so the single-post view can show "Part N of M" and let the reader walk the series.
    protected function seriesContext(): ?array
    {
        if (! $this->post->series_id) {
            return null;
        }

        $posts = $this->post->series
            ->postsInOrder()
            ->published()
            ->get(['id', 'title', 'body', 'series_part', 'published_at', 'created_at']);

        $index = $posts->search(fn (Post $p) => $p->id === $this->post->id);

        // The current post is unpublished/scheduled — nothing meaningful to anchor against.
        if ($index === false) {
            return null;
        }

        return [
            'series'   => $this->post->series,
            'posts'    => $posts,
            'position' => $index + 1,
            'total'    => $posts->count(),
            'prev'     => $index > 0 ? $posts->get($index - 1) : null,
            'next'     => $posts->get($index + 1),
        ];
    }

    protected function afterDelete(): void
    {
        $this->redirect(route('home'), navigate: true);
    }

    protected function afterMove(Post $post, Feed $target): void
    {
        // The action updated a separate route-bound instance; mirror the new feed on
        // our mounted post so the label/button flip on re-render — no query, no
        // relation reload (refresh() would re-fetch all 4 eager-loaded relations).
        $this->post->feed = $target;
    }

    public function render()
    {
        $description = $this->post->body
            ? str($this->post->body)->limit(160)->toString()
            : 'A post by '.$this->post->user->name.' on '.config('app.name').'.';

        $firstImage = $this->post->media->firstWhere('type', 'image');
        $image = $firstImage?->url();

        return view('livewire.show-post', [
            'series' => $this->seriesContext(),
        ])->layout('layouts.app', [
            'ogTitle'       => $this->post->user->name.' on '.config('app.name'),
            'ogDescription' => $description,
            'ogImage'       => $image,
            'ogUrl'         => route('posts.show', $this->post),
        ]);
    }
}
