<?php

use App\Livewire\Actions\Logout;
use App\Models\Category;
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
    public function categories()
    {
        return Category::orderBy('name')->get(['slug', 'name']);
    }

    #[Computed]
    public function tags()
    {
        return Tag::withCount('posts')->has('posts')->orderByDesc('posts_count')->limit(10)->get(['slug', 'name']);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" wire:navigate>
                        @php $logoUrl = \App\Support\SiteMedia::logoUrl(); @endphp
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" class="h-8 object-contain" />
                        @else
                            <span class="font-display text-xl font-bold tracking-tight text-brand-600 dark:text-brand-500">
                                {{ config('app.name', 'OmniGeek') }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>
                        {{ __('Feed') }}
                    </x-nav-link>

                    <x-nav-dropdown label="Geeks" routePrefix="geeks">
                        <x-slot name="items">
                            @forelse ($this->geeks as $geek)
                                <a href="{{ route('geeks.show', $geek) }}" wire:navigate
                                    class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    <x-avatar :user="$geek" size="sm" />
                                    {{ $geek->name }}
                                </a>
                            @empty
                                <span class="block px-4 py-2 text-sm text-gray-400">No geeks yet</span>
                            @endforelse
                        </x-slot>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Categories" routePrefix="categories">
                        <a href="{{ route('categories.index') }}" wire:navigate
                            class="block px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600">
                            All categories
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                        <x-slot name="items">
                            @forelse ($this->categories as $category)
                                <a href="{{ route('categories.show', $category->slug) }}" wire:navigate
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ $category->name }}
                                </a>
                            @empty
                                <span class="block px-4 py-2 text-sm text-gray-400">No categories yet</span>
                            @endforelse
                            <div class="border-t border-gray-100 dark:border-gray-600"></div>
                            <a href="{{ route('memes') }}" wire:navigate
                                class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                Memes
                            </a>
                        </x-slot>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Tags" routePrefix="tags">
                        <a href="{{ route('tags.index') }}" wire:navigate
                            class="block px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-600">
                            Browse &amp; filter tags
                        </a>
                        <div class="border-t border-gray-100 dark:border-gray-600"></div>
                        <x-slot name="items">
                            @forelse ($this->tags as $tag)
                                <a href="{{ route('tags.show', $tag->slug) }}" wire:navigate
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    #{{ $tag->name }}
                                </a>
                            @empty
                                <span class="block px-4 py-2 text-sm text-gray-400">No tags yet</span>
                            @endforelse
                        </x-slot>
                    </x-nav-dropdown>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <form action="{{ route('search') }}" method="GET" class="flex items-center">
                    <input type="search" name="q" placeholder="Search…"
                        value="{{ request('q') }}"
                        class="w-44 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 py-1 px-2" />
                </form>
                <a href="{{ route('subscribe') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Subscribe') }}</a>
                <x-theme-toggle />
                @auth
                    <button @click="$dispatch('open-compose')"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xl leading-none font-light transition-colors"
                        title="New post">+</button>
                @endauth
                @guest
                    <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">{{ __('Log in') }}</a>
                @else
                <x-dropdown align="right" width="48" contentClasses="py-1 bg-white dark:bg-gray-700">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @can('admin')
                            <x-dropdown-link :href="route('admin.home')" wire:navigate>
                                {{ __('Admin') }}
                            </x-dropdown-link>
                        @endcan

                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
                @endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center gap-2 sm:hidden">
                @auth
                    <button @click="$dispatch('open-compose')"
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xl leading-none font-light transition-colors"
                        title="New post">+</button>
                @endauth
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <div class="px-4 pb-2">
                <form action="{{ route('search') }}" method="GET">
                    <input type="search" name="q" placeholder="Search…"
                        value="{{ request('q') }}"
                        class="w-full text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md focus:border-brand-500 focus:ring-brand-500 py-1.5 px-3" />
                </form>
            </div>
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>
                {{ __('Feed') }}
            </x-responsive-nav-link>

            <div class="px-4 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Geeks') }}</div>
            @foreach ($this->geeks as $geek)
                <x-responsive-nav-link :href="route('geeks.show', $geek)" :active="request()->routeIs('geeks.show') && request()->route('user')?->is($geek)" wire:navigate>
                    {{ $geek->name }}
                </x-responsive-nav-link>
            @endforeach

            <div class="px-4 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Categories') }}</div>
            <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.index')" wire:navigate>
                {{ __('All categories') }}
            </x-responsive-nav-link>
            @foreach ($this->categories as $category)
                <x-responsive-nav-link :href="route('categories.show', $category->slug)" wire:navigate>
                    {{ $category->name }}
                </x-responsive-nav-link>
            @endforeach
            <x-responsive-nav-link :href="route('memes')" :active="request()->routeIs('memes')" wire:navigate>
                {{ __('Memes') }}
            </x-responsive-nav-link>

            <div class="px-4 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('Tags') }}</div>
            <x-responsive-nav-link :href="route('tags.index')" :active="request()->routeIs('tags.index')" wire:navigate>
                {{ __('Browse & filter tags') }}
            </x-responsive-nav-link>
            @foreach ($this->tags as $tag)
                <x-responsive-nav-link :href="route('tags.show', $tag->slug)" wire:navigate>
                    #{{ $tag->name }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700">
            <div class="mb-2">
                <x-responsive-nav-link :href="route('subscribe')" :active="request()->routeIs('subscribe')" wire:navigate>
                    {{ __('Subscribe') }}
                </x-responsive-nav-link>
            </div>
            <div class="px-4 pb-3">
                <x-theme-toggle />
            </div>
            @guest
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" wire:navigate>
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                </div>
            @else
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @can('admin')
                    <x-responsive-nav-link :href="route('admin.home')" wire:navigate>
                        {{ __('Admin') }}
                    </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
            @endguest
        </div>
    </div>
</nav>
