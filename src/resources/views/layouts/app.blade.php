<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $ogTitle       = $ogTitle       ?? config('app.name');
            $ogDescription = $ogDescription ?? config('app.name').' — a small invite-only mini-blog for geeks.';
            $ogImage       = $ogImage       ?? \App\Support\SiteMedia::ogDefaultUrl();
            $ogUrl         = $ogUrl         ?? url()->current();
        @endphp

        <title>{{ $ogTitle === config('app.name') ? $ogTitle : $ogTitle.' — '.config('app.name') }}</title>

        <meta name="description" content="{{ $ogDescription }}">

        <!-- Open Graph -->
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title"       content="{{ $ogTitle }}">
        <meta property="og:description" content="{{ $ogDescription }}">
        <meta property="og:url"         content="{{ $ogUrl }}">
        <meta property="og:type"        content="website">
        @if ($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
            <meta property="og:image:width"  content="1200">
            <meta property="og:image:height" content="630">
        @endif

        <!-- Twitter Card -->
        <meta name="twitter:card"        content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title"       content="{{ $ogTitle }}">
        <meta name="twitter:description" content="{{ $ogDescription }}">
        @if ($ogImage)
            <meta name="twitter:image" content="{{ $ogImage }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=lato:400,700|poppins:500,600,700&display=swap" rel="stylesheet" />

        @include('partials.dark-mode-head')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @auth
        <div
            x-data="{ open: false }"
            x-on:open-compose.window="open = true"
            x-on:close-compose.window="open = false"
            x-on:keydown.escape.window="open = false"
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-start justify-center pt-16 px-4"
        >
            {{-- backdrop --}}
            <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

            {{-- modal panel --}}
            <div class="relative z-10 w-full max-w-2xl bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">New post</h2>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
                </div>
                <livewire:compose-post />
            </div>
        </div>
        @endauth
    </body>
</html>
