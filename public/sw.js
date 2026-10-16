const CACHE_NAME = 'diskominfo-inventaris-v2';
const STATIC_ASSETS = [
    '/manifest.webmanifest',
    '/images/logo-diskominfo.png',
    '/images/fallback-torn-paper.svg',
    '/images/no-image.png',
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
        })
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    // Cache-first for static images & fonts, Network-first for HTML/API
    if (
        url.pathname.startsWith('/images/') ||
        url.pathname.startsWith('/build/') ||
        url.pathname === '/manifest.webmanifest'
    ) {
        event.respondWith(
            caches.match(req).then((cached) => {
                const fetchPromise = fetch(req)
                    .then((networkRes) => {
                        if (networkRes && networkRes.status === 200) {
                            const copy = networkRes.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(req, copy));
                        }
                        return networkRes;
                    })
                    .catch(() => cached);
                return cached || fetchPromise;
            })
        );
    }
});

// Handle Push / Local ServiceWorker Notifications
self.addEventListener('push', (event) => {
    let payload = {
        title: 'Inventaris Diskominfo Garut',
        body: 'Ada pembaruan data inventaris atau persetujuan mutasi.',
        url: '/notifications',
        icon: '/images/logo-diskominfo.png',
    };

    if (event.data) {
        try {
            const parsed = event.data.json();
            payload = Object.assign(payload, parsed);
        } catch (e) {
            payload.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon || '/images/logo-diskominfo.png',
            badge: '/images/logo-diskominfo.png',
            vibrate: [120, 60, 120],
            data: {
                url: payload.url || '/dashboard',
            },
        })
    );
});

// Seamless Deep-Link Navigation when user taps notification on Mobile/Tablet/Desktop
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const targetUrl = (event.notification.data && event.notification.data.url) || '/dashboard';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.focus();
                    if ('navigate' in client) {
                        return client.navigate(targetUrl);
                    }
                    client.postMessage({ type: 'NAVIGATE_DEEP_LINK', url: targetUrl });
                    return;
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(targetUrl);
            }
        })
    );
});
