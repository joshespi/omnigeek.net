<div class="max-w-3xl mx-auto px-4 py-8" wire:poll.30s="refreshFeed">
    <x-posts-feed :posts="$posts" />
</div>
