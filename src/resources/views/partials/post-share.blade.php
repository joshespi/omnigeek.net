@props(['post'])

@php
    $url   = route('posts.show', $post);
    $title = $post->preview(120) ?? 'A post on '.config('app.name');

    // Shared styling for the square platform-icon links.
    $iconLink = 'inline-flex items-center justify-center w-9 h-9 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600';
@endphp

<div
    x-data="{
        url: @js($url),
        title: @js($title),
        copied: false,
        canShare: !! (navigator.share),
        copy() {
            navigator.clipboard.writeText(this.url);
            this.copied = true;
            setTimeout(() => this.copied = false, 1500);
        },
        nativeShare() {
            navigator.share({ title: this.title, url: this.url }).catch(() => {});
        },
    }"
    class="mt-6 border-t border-gray-100 dark:border-gray-700 pt-4"
>
    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Share this post</p>

    <div class="flex flex-wrap items-center gap-2">
        {{-- Copy link --}}
        <button type="button" x-on:click="copy()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5M10.172 13.828a4 4 0 010-5.656l3-3a4 4 0 115.656 5.656l-1.5 1.5"/>
            </svg>
            <span x-show="! copied">Copy link to share elsewhere</span>
            <span x-show="copied" x-cloak class="text-green-600 dark:text-green-400">Copied!</span>
        </button>

        {{-- Native share (mobile / supported browsers) --}}
        <button type="button" x-show="canShare" x-cloak x-on:click="nativeShare()"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-md bg-brand-600 text-white hover:bg-brand-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            Share…
        </button>

        {{-- Platform links --}}
        <a target="_blank" rel="noopener noreferrer"
            href="https://twitter.com/intent/tweet?text={{ rawurlencode($title) }}&url={{ rawurlencode($url) }}"
            class="{{ $iconLink }}" title="Share on X" aria-label="Share on X">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>

        <a target="_blank" rel="noopener noreferrer"
            href="https://bsky.app/intent/compose?text={{ rawurlencode($title.' '.$url) }}"
            class="{{ $iconLink }}" title="Share on Bluesky" aria-label="Share on Bluesky">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 01-.415-.056c.14.017.279.036.415.056 2.67.296 5.568-.628 6.383-3.364.246-.828.624-5.789.624-6.479 0-.688-.139-1.86-.902-2.203-.659-.299-1.664-.621-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>
        </a>

        <a target="_blank" rel="noopener noreferrer"
            href="https://www.reddit.com/submit?url={{ rawurlencode($url) }}&title={{ rawurlencode($title) }}"
            class="{{ $iconLink }}" title="Share on Reddit" aria-label="Share on Reddit">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0zm5.01 4.744c.688 0 1.25.561 1.25 1.249a1.25 1.25 0 01-2.498.056l-2.597-.547-.8 3.747c1.824.07 3.48.632 4.674 1.488.308-.309.73-.491 1.207-.491.968 0 1.754.786 1.754 1.754 0 .716-.435 1.333-1.01 1.614a3.111 3.111 0 01.042.52c0 2.694-3.13 4.87-7.004 4.87-3.874 0-7.004-2.176-7.004-4.87 0-.183.015-.366.043-.534A1.748 1.748 0 014.028 12c0-.968.786-1.754 1.754-1.754.463 0 .898.196 1.207.49 1.207-.883 2.878-1.43 4.744-1.487l.885-4.182a.342.342 0 01.14-.197.35.35 0 01.238-.042l2.906.617a1.214 1.214 0 011.108-.701zM9.25 12C8.561 12 8 12.562 8 13.25c0 .687.561 1.248 1.25 1.248.687 0 1.248-.561 1.248-1.249 0-.688-.561-1.249-1.249-1.249zm5.5 0c-.687 0-1.248.561-1.248 1.25 0 .687.561 1.248 1.249 1.248.688 0 1.249-.561 1.249-1.249 0-.687-.562-1.249-1.25-1.249zm-5.466 3.99a.327.327 0 00-.231.094.33.33 0 000 .463c.842.842 2.484.913 2.961.913.477 0 2.105-.07 2.961-.913a.361.361 0 00.029-.463.33.33 0 00-.464 0c-.547.533-1.684.73-2.512.73-.828 0-1.979-.196-2.512-.73a.326.326 0 00-.232-.095z"/></svg>
        </a>

        <a target="_blank" rel="noopener noreferrer"
            href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($url) }}"
            class="{{ $iconLink }}" title="Share on Facebook" aria-label="Share on Facebook">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        </a>
    </div>
</div>
