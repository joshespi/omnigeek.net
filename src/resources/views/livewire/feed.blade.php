<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="sr-only">{{ config('app.name') }} — latest posts</h1>
    <x-posts-feed :posts="$posts" />
</div>
