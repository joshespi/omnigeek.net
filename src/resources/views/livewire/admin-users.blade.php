<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-display font-bold text-gray-900 dark:text-gray-100">Users</h1>
        <a href="{{ route('admin.home') }}" wire:navigate class="text-sm text-brand-600 dark:text-brand-400 hover:underline">← Admin</a>
    </div>

    {{-- Invite form --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 mb-6">
        <h2 class="font-medium text-gray-900 dark:text-gray-100 mb-3">Send invite</h2>

        @if ($inviteLink)
            <div class="mb-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-md text-sm">
                <p class="text-green-800 dark:text-green-300 font-medium mb-1">Invite link ready:</p>
                <code class="break-all text-green-700 dark:text-green-400 select-all">{{ $inviteLink }}</code>
                <button wire:click="$set('inviteLink', null)" class="block mt-2 text-xs text-gray-500 hover:underline">Dismiss</button>
            </div>
        @endif

        <form wire:submit="sendInvite" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <x-input-label for="inviteEmail" value="Email (optional — leave blank for open)" />
                <x-text-input wire:model="inviteEmail" id="inviteEmail" type="email"
                    class="mt-1 block w-full" placeholder="friend@example.com" />
                <x-input-error :messages="$errors->get('inviteEmail')" class="mt-1" />
            </div>
            <div class="w-28">
                <x-input-label for="inviteDays" value="Expires (days)" />
                <x-text-input wire:model="inviteDays" id="inviteDays" type="number"
                    class="mt-1 block w-full" min="0" max="365" />
            </div>
            <x-primary-button>Generate link</x-primary-button>
        </form>
    </div>

    {{-- User list --}}
    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg divide-y divide-gray-100 dark:divide-gray-700">
        @forelse ($users as $user)
            <div wire:key="user-{{ $user->id }}" class="p-4">
                @if ($editingId === $user->id)
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <x-input-label value="Name" />
                                <x-text-input wire:model="editingName" type="text" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label value="Email" />
                                <x-text-input wire:model="editingEmail" type="email" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('editingEmail')" class="mt-1" />
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Bio" />
                            <textarea wire:model="editingBio"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                            <x-input-error :messages="$errors->get('editingBio')" class="mt-1" />
                        </div>
                        <div class="flex items-center gap-3">
                            <x-primary-button wire:click="update">Save</x-primary-button>
                            <button wire:click="cancelEdit" type="button"
                                class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">Cancel</button>
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <x-avatar :user="$user" size="md" />
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                                    @if ($user->is_admin)
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300">admin</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                <p class="text-xs text-gray-400">{{ $user->posts_count }} {{ Str::plural('post', $user->posts_count) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm shrink-0">
                            <button wire:click="edit({{ $user->id }})"
                                class="text-brand-600 dark:text-brand-400 hover:underline">Edit</button>
                            @if ($user->id !== auth()->id())
                                <button wire:click="toggleAdmin({{ $user->id }})"
                                    wire:confirm="{{ $user->is_admin ? 'Remove admin?' : 'Make admin?' }}"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    {{ $user->is_admin ? 'Revoke admin' : 'Make admin' }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="p-6 text-center text-gray-500">No users yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
