<?php

use App\Livewire\Actions\Logout;
use App\Models\Tag;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    #[Computed]
    public function geeks()
    {
        return User::has('posts')->orderBy('name')->get(['id', 'name', 'avatar_path']);
    }

    #[Computed]
    public function tags()
    {
        return Tag::withCount('posts')->has('posts')->orderByDesc('posts_count')->limit(10)->get(['slug', 'name']);
    }
}; ?>

<header
    x-data="{ open: false }"
    x-on:livewire:navigating.document="open = false"
    x-on:keydown.escape="if (open) { open = false; $refs.menuToggle.focus(); }"
    class="sticky top-0 z-40 border-b border-editorial-border bg-editorial-surface"
>
    <div class="site-container flex min-h-16 items-center justify-between gap-3">
        <x-site-logo class="shrink-0" />

        <div id="site-navigation"
            :class="{ 'flex': open, 'hidden': !open }"
            class="absolute inset-x-0 top-full hidden max-h-[calc(100dvh-4rem)] flex-col gap-3 overflow-y-auto border-b border-editorial-border bg-editorial-surface px-4 py-3 lg:static lg:flex lg:max-h-none lg:flex-1 lg:flex-row lg:items-center lg:justify-between lg:gap-2 lg:overflow-visible lg:border-0 lg:p-0">
            <nav aria-label="Primary" class="flex min-w-0 flex-col lg:flex-row lg:items-center lg:gap-1 lg:[&>a]:w-auto lg:[&>a]:whitespace-nowrap">
                <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>Home</x-nav-link>

                <x-nav-dropdown label="Categories" routePrefix="categories">
                    <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.index')" wire:navigate>All categories</x-nav-link>
                    @foreach (\App\Models\Category::forNavigation() as $category)
                        <x-nav-link :href="route('categories.show', $category->slug)" wire:navigate>
                            {{ $category->name }}
                        </x-nav-link>
                    @endforeach
                    <x-nav-link :href="route('memes')" :active="request()->routeIs('memes')" wire:navigate>Memes</x-nav-link>
                </x-nav-dropdown>

                <x-nav-dropdown label="Geeks" routePrefix="geeks">
                    @forelse ($this->geeks as $geek)
                        <x-nav-link :href="route('geeks.show', $geek)" wire:navigate>
                            <x-avatar :user="$geek" size="sm" />
                            {{ $geek->name }}
                        </x-nav-link>
                    @empty
                        <p class="px-3 py-2 text-sm text-editorial-muted">No geeks yet</p>
                    @endforelse
                </x-nav-dropdown>

                <x-nav-link :href="route('series.index')" :active="request()->routeIs('series.index')"
                    :section-active="request()->routeIs('series.show')" wire:navigate>Series</x-nav-link>

                <x-nav-dropdown label="Tags" routePrefix="tags">
                    <x-nav-link :href="route('tags.index')" :active="request()->routeIs('tags.index')" wire:navigate>Browse &amp; filter tags</x-nav-link>
                    @foreach ($this->tags as $tag)
                        <x-nav-link :href="route('tags.show', $tag->slug)" wire:navigate>
                            #{{ $tag->name }}
                        </x-nav-link>
                    @endforeach
                </x-nav-dropdown>
            </nav>

            <div class="flex min-w-0 flex-col gap-2 lg:flex-row lg:items-center lg:gap-1 lg:[&>a]:w-auto lg:[&>a]:whitespace-nowrap">
                <form action="{{ route('search') }}" method="GET" role="search" class="relative order-first lg:w-36 lg:shrink-0 xl:w-48">
                    <x-text-input type="search" name="q" aria-label="Search posts" placeholder="Search posts"
                        value="{{ request()->string('q') }}" class="h-11 w-full ps-3 pe-11 text-sm" />
                    <button type="submit" class="site-control absolute inset-y-0 end-0" aria-label="Submit search">
                        @include('partials.search-icon')
                    </button>
                </form>

                <x-nav-link :href="route('subscribe')" :active="request()->routeIs('subscribe')" wire:navigate>Subscribe</x-nav-link>

                @guest
                    <x-nav-link :href="route('login')" wire:navigate>Log in</x-nav-link>
                @else
                    <x-nav-dropdown align="right">
                        <x-slot name="trigger">
                            <span class="max-w-28 truncate" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                                x-text="name" x-on:profile-updated.window="name = $event.detail.name">{{ auth()->user()->name }}</span>
                        </x-slot>
                        <x-nav-link :href="route('profile')" :active="request()->routeIs('profile')" wire:navigate>Profile</x-nav-link>
                        @can('admin')
                            <x-nav-link :href="route('admin.home')" :active="request()->routeIs('admin.home')" wire:navigate>Admin</x-nav-link>
                        @endcan
                        <button type="button" wire:click="logout" class="site-menu-link">Log out</button>
                    </x-nav-dropdown>
                @endguest
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <a href="{{ route('search') }}" wire:navigate aria-label="Search posts" class="site-control lg:hidden">
                @include('partials.search-icon')
            </a>
            <x-theme-toggle />
            @auth
                <button type="button" x-on:click="$dispatch('open-compose')" class="site-control bg-editorial-elevated text-xl"
                    title="New post" aria-label="New post">+</button>
            @endauth
            <button type="button" x-ref="menuToggle" x-on:click="open = !open"
                aria-label="Menu" aria-controls="site-navigation" :aria-expanded="open" class="site-control lg:hidden">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path x-show="!open" d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="open" x-cloak d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>
</header>
