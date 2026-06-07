# TODO / Backlog

## Features

- [ ] Remove delete button from feed cards — delete should only be accessible from the post detail screen or admin panel, not inline on the feed.
- [ ] Cross-user live feed refresh — removed the 30s `wire:poll` (was ~7,400 idle queries/hr/user). Other users' new posts now need a page reload. If real-time is wanted, do it with Laravel Echo/websockets, not polling.

## Security / hardening

- [x] Regression test pinning the upload-extension invariant: an uploaded file named `*.php` must land on disk with a server-derived safe extension (not the client extension). This is the single control preventing upload-RCE — nginx forwards `*.php` to FPM and web-serves the upload volume. Holds today; a future switch to `storeAs(getClientOriginalExtension())` would break it silently.
