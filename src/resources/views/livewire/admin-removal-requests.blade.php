<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Removal requests</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    <div class="space-y-4">
        @forelse ($requests as $request)
            <div wire:key="removal-{{ $request->id }}"
                @class([
                    'bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4',
                    'opacity-60' => $request->isResolved(),
                ])>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="text-sm">
                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $request->kindLabel() }}</span>
                        <span class="text-gray-400">· {{ $request->created_at->diffForHumans() }}</span>
                        @if ($request->isResolved())
                            <span class="ml-1 inline-block rounded-full bg-green-100 dark:bg-green-900/40 px-2 py-0.5 text-xs text-green-700 dark:text-green-300">Resolved</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        @if ($request->isResolved())
                            <button wire:click="reopen({{ $request->id }})" class="text-brand-600 dark:text-brand-400 hover:underline">Reopen</button>
                        @else
                            <button wire:click="resolve({{ $request->id }})" class="text-brand-600 dark:text-brand-400 hover:underline">Mark resolved</button>
                        @endif
                        <button wire:click="delete({{ $request->id }})" wire:confirm="Delete this request? This cannot be undone." class="text-gray-400 hover:text-red-600">Delete</button>
                    </div>
                </div>

                <p class="mt-3 text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line break-words">{{ $request->details }}</p>

                <dl class="mt-3 grid gap-1 text-sm text-gray-500 dark:text-gray-400">
                    @if ($request->post)
                        <div>
                            <dt class="inline">Post:</dt>
                            <dd class="inline">
                                <a href="{{ route('posts.show', $request->post) }}" wire:navigate class="text-brand-600 dark:text-brand-400 hover:underline">
                                    {{ $request->post->preview(80) ?? 'Post #'.$request->post->id }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if ($request->name)
                        <div><dt class="inline">From:</dt> <dd class="inline">{{ $request->name }}</dd></div>
                    @endif
                    @if ($request->email)
                        <div>
                            <dt class="inline">Reply to:</dt>
                            <dd class="inline"><a href="mailto:{{ $request->email }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $request->email }}</a></dd>
                        </div>
                    @endif
                </dl>
            </div>
        @empty
            <p class="text-center text-gray-500">No removal requests.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
</div>
