<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Posts</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($posts as $post)
            <div wire:key="post-{{ $post->id }}" class="p-4">
                @if ($editingId === $post->id)
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <x-avatar :user="$post->user" size="sm" />
                            <span>{{ $post->user->name }} · {{ $post->displayDate()->diffForHumans() }}</span>
                        </div>

                        <div>
                            <x-input-label value="Title (optional)" />
                            <x-text-input wire:model="form.title" type="text" class="mt-1 block w-full" placeholder="Post title" />
                            <x-input-error :messages="$errors->get('form.title')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label value="Body" class="mb-1" />
                            <x-markdown-editor model="form.body" :body="$form->body" :show-preview="$showPreview" :rows="10" />
                            <x-input-error :messages="$errors->get('form.body')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label value="Categories" />
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" wire:model="form.selectedCategories" value="{{ $cat->id }}"
                                            class="rounded border-gray-300 dark:border-gray-700 text-brand-600" />
                                        {{ $cat->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Tags (space or comma separated)" />
                            <x-text-input wire:model="form.tags" type="text" class="mt-1 block w-full" placeholder="#rust gaming" />
                            <x-input-error :messages="$errors->get('form.tags')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label value="Post date (leave blank to publish immediately)" />
                            <input type="datetime-local" wire:model="form.publishedAt"
                                class="mt-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 text-sm" />
                            <x-input-error :messages="$errors->get('form.publishedAt')" class="mt-1" />
                        </div>

                        <div class="flex items-center gap-3 pt-1">
                            <x-primary-button wire:click="update">Save</x-primary-button>
                            <button wire:click="cancelEdit" type="button"
                                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        </div>
                    </div>
                @else
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-1">
                                <x-avatar :user="$post->user" size="sm" />
                                <a href="{{ route('geeks.show', $post->user) }}" wire:navigate class="hover:underline">{{ $post->user->name }}</a>
                                <span>·</span>
                                <a href="{{ route('posts.show', $post) }}" wire:navigate class="hover:underline">{{ $post->displayDate()->diffForHumans() }}</a>
                                @if ($post->published_at && $post->published_at->isFuture())
                                    <span class="text-xs px-1.5 py-0.5 rounded bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300">Scheduled</span>
                                @endif
                            </div>

                            @if ($post->title)
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-0.5">{{ $post->title }}</p>
                            @endif

                            @if ($post->body)
                                <p class="text-sm text-gray-800 dark:text-gray-200 line-clamp-2 mb-1">{{ $post->body }}</p>
                            @endif

                            @if ($post->media_path)
                                <span class="text-xs text-gray-400">{{ $post->media_type === 'video' ? '▶ Video' : '🖼 Image' }}</span>
                            @endif

                            @if ($post->youtube_id)
                                <span class="text-xs text-gray-400">▶ YouTube</span>
                            @endif

                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach ($post->categories as $cat)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $cat->name }}</span>
                                @endforeach
                                @foreach ($post->tags as $tag)
                                    <span class="text-xs text-brand-600 dark:text-brand-400">#{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm shrink-0">
                            @if ($post->view_count > 0)
                                <span class="text-xs text-gray-400">{{ number_format($post->view_count) }} views</span>
                            @endif
                            <button wire:click="edit({{ $post->id }})"
                                class="text-brand-600 dark:text-brand-400 hover:underline">Edit</button>
                            <button wire:click="deletePost({{ $post->id }})"
                                wire:confirm="Delete this post permanently?"
                                class="text-gray-400 hover:text-red-600">Delete</button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="p-6 text-center text-gray-500">No posts yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
</div>
