<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>
    {{-- Self-contained on purpose: 500/503 may mean the DB or asset pipeline is down,
         and 429 shouldn't run nav queries for a client we're throttling — so these
         pages can't depend on the app layout. Mirrors public/offline.html. --}}
    @include('partials.dark-mode-head')
    <style>
        :root { color-scheme: light; --canvas: #f7f8fb; --heading: #171b26; --text: #414958; --accent: #6247d5; }
        :root.dark { color-scheme: dark; --canvas: #11151d; --heading: #f3f5fa; --text: #c0c6d2; --accent: #a99aff; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: system-ui, sans-serif; background: var(--canvas); color: var(--heading); text-align: center; }
        main { padding: 1.5rem; }
        p { color: var(--text); } a { color: var(--accent); }
    </style>
</head>
<body>
    <main>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="/">Back to the feed</a>
    </main>
</body>
</html>
