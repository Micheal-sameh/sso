// Deliberately conservative: this app serves authenticated, per-user HTML
// (Inertia pages carrying CSRF tokens and session-scoped props), so we never
// cache navigations or API/data responses — only the versioned, immutable
// Vite build assets (hashed filenames) are safe to cache aggressively.
const CACHE_NAME = 'avarewase-static-v1';
const STATIC_PATH_PREFIXES = ['/build/', '/icons/'];

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))),
        ),
    );
    self.clients.claim();
});

function isStaticAsset(url) {
    return STATIC_PATH_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
}

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(
            caches.open(CACHE_NAME).then(async (cache) => {
                const cached = await cache.match(event.request);
                if (cached) return cached;

                const response = await fetch(event.request);
                if (response.ok) cache.put(event.request, response.clone());
                return response;
            }),
        );
        return;
    }

    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(
                () =>
                    new Response(
                        '<!doctype html><html><head><meta charset="utf-8"><title>Offline</title></head>' +
                            '<body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;' +
                            'height:100vh;margin:0;background:#F8F9FA;color:#1B2A4A;text-align:center;padding:24px">' +
                            '<div><h1>You’re offline</h1><p>Check your connection and try again.</p></div></body></html>',
                        { headers: { 'Content-Type': 'text/html' } },
                    ),
            ),
        );
    }
});
