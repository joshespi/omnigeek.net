<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="512x512" href="/icons/web-app-manifest-512x512.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/icons/web-app-manifest-192x192.png">

        <!-- PWA -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#7c3aed">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="OmniGeek">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

        <!-- Fonts -->
        @include('partials.editorial-fonts')
        @include('partials.dark-mode-head')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-editorial-canvas font-sans text-editorial-body antialiased">
        <main class="site-container flex min-h-screen flex-col items-center py-8 sm:justify-center">
            <div class="flex w-full max-w-md items-center justify-between gap-4">
                <x-site-logo />
                <x-theme-toggle />
            </div>

            <div class="mt-4 w-full max-w-md rounded-xl border border-editorial-border bg-editorial-surface px-6 py-5">
                {{ $slot }}
            </div>
        </main>
    </body>
</html>
