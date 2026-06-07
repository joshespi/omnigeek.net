<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Activity Log</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    @php
    $labels = [
        'post.create'        => 'Created post',
        'post.update'        => 'Edited post',
        'post.delete'        => 'Deleted post',
        'post.move'          => 'Moved post',
        'user.update'        => 'Edited user',
        'user.delete'        => 'Deleted user',
        'user.toggle_admin'  => 'Toggled admin',
        'category.create'    => 'Created category',
        'category.update'    => 'Renamed category',
        'category.delete'    => 'Deleted category',
    ];
    @endphp

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($entries as $entry)
            <div class="px-4 py-3 flex items-start gap-3 text-sm">
                <div class="shrink-0 w-32 text-xs text-gray-400 dark:text-gray-500 pt-0.5">
                    {{ $entry->created_at->diffForHumans() }}
                </div>
                <div class="shrink-0 w-28 font-medium text-gray-700 dark:text-gray-300">
                    {{ $entry->user->name ?? '—' }}
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-gray-900 dark:text-gray-100">{{ $labels[$entry->action] ?? $entry->action }}</span>
                    @if ($entry->subject_label)
                        <span class="text-gray-500 dark:text-gray-400">: {{ $entry->subject_label }}</span>
                    @endif
                    @if ($entry->action === 'user.toggle_admin' && isset($entry->meta['is_admin']))
                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded {{ $entry->meta['is_admin'] ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }}">
                            {{ $entry->meta['is_admin'] ? 'granted' : 'revoked' }}
                        </span>
                    @endif
                    @if ($entry->action === 'post.move' && isset($entry->meta['to']))
                        <span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">→ {{ $entry->meta['to'] }}</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-6 text-center text-gray-500 dark:text-gray-400">No activity yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
