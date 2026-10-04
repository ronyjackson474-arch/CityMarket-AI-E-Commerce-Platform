/*
 * CityMarket Service Worker
 * - Precache app shell (offline fallback)
 * - Network-first for pages, cache-first for static assets & images
 */
const VERSION = 'v1';
const SHELL_CACHE = `cm-shell-${VERSION}`;
const PAGE_CACHE = `cm-pages-${VERSION}`;
const ASSET_CACHE = `cm-assets-${VERSION}`;
const IMG_CACHE = `cm-img-${VERSION}`;

const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
    OFFLINE_URL,
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/manifest.json',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => cache.addAll(PRECACHE_URLS)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => ![SHELL_CACHE, PAGE_CACHE, ASSET_CACHE, IMG_CACHE].includes(k))
                    .map((k) => caches.delete(k))
            ).then(() => self.clients.claim())
        )
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Never cache admin, API, or authenticated dynamic endpoints
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api') ||
        url.pathname.startsWith('/cart') || url.pathname.startsWith('/wishlist') ||
        url.pathname.startsWith('/checkout') || url.pathname.startsWith('/account') ||
        url.pathname.startsWith('/ai/chat')) {
        return;
    }

    // Static assets (Vite build) — cache-first
    if (url.pathname.startsWith('/build/') || url.pathname.match(/\.(css|js|woff2?)$/)) {
        event.respondWith(
            caches.match(request).then((cached) =>
                cached ||
                fetch(request).then((response) => {
                    const copy = response.clone();
                    caches.open(ASSET_CACHE).then((cache) => cache.put(request, copy));
                    return response;
                })
            )
        );
        return;
    }

    // Images — cache-first with size cap
    if (request.destination === 'image' || url.pathname.startsWith('/storage/') || url.pathname.startsWith('/icons/')) {
        event.respondWith(
            caches.open(IMG_CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                if (cached) return cached;
                try {
                    const response = await fetch(request);
                    if (response.ok) cache.put(request, response.clone());
                    return response;
                } catch (e) {
                    return cached || Response.error();
                }
            })
        );
        return;
    }

    // Pages — network-first with offline fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(PAGE_CACHE).then((cache) => cache.put(request, copy));
                    return response;
                })
                .catch(() =>
                    caches.match(request).then((cached) => cached || caches.match(OFFLINE_URL))
                )
        );
    }
});
