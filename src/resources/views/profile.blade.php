<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">
            {{ __('Profile') }}
        </h1>
    </x-slot>

    <div class="py-12">
        <div class="site-container space-y-6">
            <div class="rounded-xl border border-editorial-border bg-editorial-surface p-5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-avatar-form />
                </div>
            </div>

            <div class="rounded-xl border border-editorial-border bg-editorial-surface p-5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="rounded-xl border border-editorial-border bg-editorial-surface p-5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <div class="rounded-xl border border-editorial-border bg-editorial-surface p-5 sm:p-8">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
