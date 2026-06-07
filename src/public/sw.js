const CACHE = 'omnigeek-v1';

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()));

self.addEventListener('fetch', e => {
    // Only cache same-origin GET requests for navigation (shell caching).
    if (e.request.method !== 'GET') return;
    if (e.request.mode === 'navigate') {
        e.respondWith(fetch(e.request).catch(() => caches.match('/')));
    }
});
