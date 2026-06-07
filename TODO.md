# TODO / Backlog

## Features

- [x] Remove delete button from feed cards — delete should only be accessible from the post detail screen or admin panel, not inline on the feed.
- [ ] Cross-user live feed refresh — removed the 30s `wire:poll` (was ~7,400 idle queries/hr/user). Other users' new posts now need a page reload. If real-time is wanted, do it with Laravel Echo/websockets, not polling.
