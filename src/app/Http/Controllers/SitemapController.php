<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Series;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public const CACHE_KEY = 'sitemap.xml';

    // [model class, route name, priority] — each has an identical "list its
    // slugged, published-post-having records" shape, only these three differ.
    private const TAXONOMIES = [
        [Category::class, 'categories.show', '0.5'],
        [Tag::class, 'tags.show', '0.4'],
        [Series::class, 'series.show', '0.5'],
    ];

    public function __invoke(): Response
    {
        return $this->cachedXmlResponse(self::CACHE_KEY, now()->addHour(), 'sitemap', [
            'urls' => $this->urls(),
        ], 'text/xml; charset=UTF-8');
    }

    private function urls(): Collection
    {
        $urls = collect([
            ['loc' => route('home'), 'changefreq' => 'hourly', 'priority' => '1.0'],
            ['loc' => route('memes'), 'changefreq' => 'hourly', 'priority' => '0.7'],
            ['loc' => route('categories.index'), 'changefreq' => 'weekly', 'priority' => '0.5'],
            ['loc' => route('tags.index'), 'changefreq' => 'weekly', 'priority' => '0.5'],
        ]);

        foreach (self::TAXONOMIES as [$model, $routeName, $priority]) {
            $model::whereHas('posts', fn ($q) => $q->published())->orderBy('name')->get()
                ->each(fn ($record) => $urls->push([
                    'loc' => route($routeName, $record), 'changefreq' => 'weekly', 'priority' => $priority,
                ]));
        }

        // Both feeds are publicly readable, so both belong in the sitemap — but
        // not NSFW memes, which are click-through-gated and shouldn't surface
        // in search results.
        Post::anyFeed()->published()->where('nsfw', false)
            ->select('id', 'updated_at')
            ->chunkById(500, function ($posts) use ($urls) {
                foreach ($posts as $post) {
                    $urls->push([
                        'loc' => route('posts.show', $post),
                        'lastmod' => $post->updated_at->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ]);
                }
            });

        return $urls;
    }
}
