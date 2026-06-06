<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OmniGeek') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=lato:400,700|poppins:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
    <div class="min-h-screen flex flex-col">
        <header class="w-full max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
            <span class="font-display text-xl font-bold tracking-tight text-brand-500">{{ config('app.name', 'OmniGeek') }}</span>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">Go to feed →</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">Log in</a>
            @endauth
        </header>

        <main class="flex-1 flex items-center">
            <div class="w-full max-w-3xl mx-auto px-6 text-center">
                <h1 class="font-display text-4xl sm:text-5xl font-bold tracking-tight">
                    A small place to post.
                </h1>
                <p class="mt-4 text-lg text-gray-400">
                    Mini-blogging for the group. Short posts, pictures, video, YouTube embeds.
                    Invite-only, no algorithm, none of the noise.
                </p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="inline-flex items-center px-5 py-3 bg-brand-600 hover:bg-brand-500 rounded-md font-medium">
                            Open the feed
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-5 py-3 bg-brand-600 hover:bg-brand-500 rounded-md font-medium">
                            Log in
                        </a>
                    @endauth
                </div>
                <p class="mt-6 text-sm text-gray-500">
                    Registration is invite-only. Ask whoever runs this for a link.
                </p>
            </div>
        </main>

        <footer class="w-full max-w-5xl mx-auto px-6 py-6 text-sm text-gray-600">
            {{ config('app.name', 'OmniGeek') }}
        </footer>
    </div>
</body>
</html>
