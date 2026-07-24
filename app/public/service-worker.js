const CACHE_VERSION = 'v1';
const STATIC_CACHE = `orzeszek-static-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/offline.html',
  '/css/styles.css',
  '/js/main.js',
  '/assets/icons/icon-192.svg',
  '/assets/icons/icon-512.svg',
  '/assets/icons/favicon.svg'
];

const PUBLIC_NAV_ROUTES = new Set([
  '/',
  '/index.php',
  '/login.php',
  '/register.php'
]);

const NETWORK_ONLY_PREFIXES = [
  '/learn.php',
  '/quiz.php',
  '/profile.php',
  '/admin/',
  '/api/'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key.startsWith('orzeszek-') && key !== STATIC_CACHE)
          .map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;

  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  if (url.origin !== self.location.origin) {
    return;
  }

  const isNetworkOnly = NETWORK_ONLY_PREFIXES.some((prefix) => url.pathname.startsWith(prefix));
  if (isNetworkOnly) {
    event.respondWith(fetch(request));
    return;
  }

  const isStaticAsset =
    url.pathname.startsWith('/css/') ||
    url.pathname.startsWith('/js/') ||
    url.pathname.startsWith('/assets/icons/') ||
    url.pathname === '/manifest.json';

  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) {
          return cached;
        }

        return fetch(request).then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, responseClone));
          }
          return response;
        });
      })
    );
    return;
  }

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() => {
        if (PUBLIC_NAV_ROUTES.has(url.pathname)) {
          return caches.match(OFFLINE_URL);
        }

        return new Response(
          '<!doctype html><html lang="pl"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Brak połączenia</title><body style="font-family:Arial,sans-serif;padding:24px;"><h1>Brak połączenia z internetem</h1><p>Ta strona wymaga połączenia z siecią.</p><p><a href="/login.php">Wróć do logowania</a></p></body></html>',
          {
            headers: { 'Content-Type': 'text/html; charset=UTF-8' },
            status: 503
          }
        );
      })
    );
  }
});
