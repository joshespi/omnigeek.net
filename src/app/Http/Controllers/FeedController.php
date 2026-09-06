<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public const CACHE_KEY = 'feed.rss';

    public function __invoke(): Response
    {
        return $this->cachedXmlResponse(self::CACHE_KEY, now()->addMinutes(15), 'feed', [
            // Main feed only (default scope) — memes are opt-in/NSFW-gated and don't
            // belong pushed into a reader or inbox. Only the relations feed.blade.php
            // actually reads — tags/media/series would be wasted eager-loading here.
            'posts' => Post::with('user', 'categories')
                ->published()
                ->latestForFeed()
                ->limit(30)
                ->get(),
        ], 'application/rss+xml; charset=UTF-8');
    }
}
