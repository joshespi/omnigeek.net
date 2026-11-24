@props(['post', 'movable' => false])

@if ($post->canDelete(auth()->user()))
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
        @if ($movable)
            @php($moveTarget = $post->oppositeFeed()->label())
            <button type="button" wire:click="moveToOtherFeed({{ $post->id }})"
                wire:confirm="Move this post to the {{ $moveTarget }}?"
                class="site-link min-h-11">Move to {{ $moveTarget }}</button>
        @endif
        <button type="button" wire:click="deletePost({{ $post->id }})"
            wire:confirm="Delete this post?"
            class="min-h-11 text-red-700 hover:underline dark:text-red-300">Delete</button>
    </div>
@endif
