// Minimal service worker: makes the app installable and keeps the compiled
// CSS/JS/icons available offline. Pages and API calls always go to the
// network first — this is a live business dashboard, not an offline-first
// app, so stale stock/finance data would be actively misleading.
const CACHE_NAME = 'bms-static-v1';
const STATIC_PATTERNS = [/^\/build\//, /^\/icons\//, /^\/vendor\//, /^\/manifest\.json$/];

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

function isStaticAsset(url) {
    return STATIC_PATTERNS.some((pattern) => pattern.test(url.pathname));
}

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
                const clone = response.clone();
                // Concurrent requests for the same asset (common for icons/fonts
                // referenced more than once on a page) can race here — both miss
                // the cache and both call put() for the same key. The loser throws
                // InvalidAccessError even though the entry ends up cached correctly,
                // so that specific race is safe to swallow.
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone).catch(() => {}));
                return response;
            })),
        );
    }
});
