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
    </body>
</html>
