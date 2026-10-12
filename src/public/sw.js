const CACHE = 'omnigeek-v2';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.add(OFFLINE_URL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', e => {
    // Drop caches from older versions of this worker.
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', e => {
    // Pages are always network-first; the offline page is only the fallback when the
    // network is unreachable (a cached copy of '/' would be stale or empty anyway).
    if (e.request.method !== 'GET' || e.request.mode !== 'navigate') return;
    e.respondWith(fetch(e.request).catch(() => caches.match(OFFLINE_URL).then(r => r || Response.error())));
});
