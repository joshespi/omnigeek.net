<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

abstract class Controller
{
    // Shared by the sitemap/RSS controllers: build (and cache) an XML view, then
    // wrap it in a response with the right content type.
    protected function cachedXmlResponse(string $cacheKey, \DateTimeInterface|\DateInterval|int $ttl, string $view, array $data, string $contentType): Response
    {
        $xml = Cache::remember($cacheKey, $ttl, fn () => view($view, $data)->render());

        return response($xml, 200)->header('Content-Type', $contentType);
    }
}
