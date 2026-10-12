<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title')</title>
    {{-- Self-contained on purpose: 500/503 may mean the DB or asset pipeline is down,
         and 429 shouldn't run nav queries for a client we're throttling — so these
         pages can't depend on the app layout. Mirrors public/offline.html. --}}
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; background: #f3f4f6; color: #111827; text-align: center; }
        @media (prefers-color-scheme: dark) { body { background: #111827; color: #f3f4f6; } p { color: #9ca3af; } }
        p { color: #6b7280; } a { color: #7c3aed; }
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
