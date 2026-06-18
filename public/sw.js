const CACHE_NAME = 'mymadrasah-cache-v1';
const OFFLINE_URL = '/pages/misc-error'; // We can change this to a custom offline page later

const urlsToCache = [
  '/',
  '/manifest.json',
  '/scan-ekskul'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        // Ignore caching errors for individual files so service worker installs even if one file is missing
        return Promise.all(
            urlsToCache.map(url => {
                return cache.add(url).catch(reason => {
                    console.log(`Failed to cache ${url}: ${reason}`);
                });
            })
        );
      })
  );
  self.skipWaiting();
});

self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(() => {
          // If network fails (offline), return cached offline page if navigating
          if (event.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
        });
      })
  );
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Listener for syncing offline queued attendance requests
self.addEventListener('sync', event => {
  if (event.tag === 'sync-absensi') {
    event.waitUntil(syncAbsensiData());
  }
});

async function syncAbsensiData() {
    // In a full implementation, we'd read from IndexedDB and fetch to server here.
    console.log('Service Worker: Background Sync triggered for Absensi...');
}
