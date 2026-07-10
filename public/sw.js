const CACHE_NAME = 'mymadrasah-cache-v1';
const OFFLINE_URL = '/pages/misc-error'; // We can change this to a custom offline page later

const urlsToCache = [
  '/',
  '/manifest.json',
  '/scan-ekskul',
  OFFLINE_URL
];

// Helper untuk membuka IndexedDB
function openAbsensiDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('AbsensiOfflineDB', 1);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending')) {
                db.createObjectStore('pending', { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

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
  const url = new URL(event.request.url);

  // Jangan intervensi request untuk rute-rute admin, guru, ortu, siswa, piket
  if (
    url.pathname.startsWith('/admin') ||
    url.pathname.startsWith('/guru') ||
    url.pathname.startsWith('/ortu') ||
    url.pathname.startsWith('/siswa') ||
    url.pathname.startsWith('/piket')
  ) {
    return;
  }

  if (event.request.method !== 'GET') return;

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(err => {
          // If network fails (offline), return cached offline page if navigating
          if (event.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL).then(offlineResponse => {
              return offlineResponse || Promise.reject(err);
            });
          }
          return Promise.reject(err);
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
    console.log('Service Worker: Background Sync triggered for Absensi...');
    try {
        const db = await openAbsensiDB();
        const tx = db.transaction('pending', 'readonly');
        const store = tx.objectStore('pending');
        const allItems = await new Promise((resolve, reject) => {
            const request = store.getAll();
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });

        if (!allItems || allItems.length === 0) {
            console.log('Service Worker: No pending absensi data to sync.');
            db.close();
            return;
        }

        for (const item of allItems) {
            try {
                const response = await fetch(item.url, {
                    method: item.method || 'POST',
                    headers: item.headers || { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item.body),
                });

                if (response.ok) {
                    const deleteTx = db.transaction('pending', 'readwrite');
                    const deleteStore = deleteTx.objectStore('pending');
                    await new Promise((resolve, reject) => {
                        const req = deleteStore.delete(item.id);
                        req.onsuccess = () => resolve();
                        req.onerror = () => reject(req.error);
                    });
                    console.log('Service Worker: Absensi data synced successfully:', item.id);
                } else {
                    console.warn('Service Worker: Failed to sync item, will retry later:', item.id, response.status);
                }
            } catch (err) {
                console.error('Service Worker: Error syncing item:', item.id, err);
                // Biarkan item tetap di IndexedDB, akan dicoba lagi nanti
            }
        }

        db.close();
    } catch (err) {
        console.error('Service Worker: Error accessing IndexedDB:', err);
    }
}
