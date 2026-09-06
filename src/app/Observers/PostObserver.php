<?php

namespace App\Observers;

use App\Http\Controllers\FeedController;
use App\Http\Controllers\SitemapController;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    // Cheap to invalidate eagerly rather than wait out the sitemap/RSS cache
    // window — a freshly published or deleted post should show up right away.
    public function saved(Post $post): void
    {
        $this->forgetPublicCaches();
    }

    public function deleted(Post $post): void
    {
        $this->forgetPublicCaches();
    }

    protected function forgetPublicCaches(): void
    {
        Cache::forget(SitemapController::CACHE_KEY);
        Cache::forget(FeedController::CACHE_KEY);
    }
}
