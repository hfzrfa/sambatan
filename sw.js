const CACHE_NAME = 'sambatan-v1';
const urlsToCache = [
  '/',
  '/css/styles.css',
  '/css/responsive.css',
  '/js/index.js',
  '/assets/sambatan.png',
  '/assets/sambatanlogo.png',
  '/assets/background.png',
  '/assets/kopi.png',
  '/menu.php',
  '/order.php',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// Install event - cache resources
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Fetch event - serve cached content
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version or fetch from network
        if (response) {
          return response;
        }
        return fetch(event.request);
      }
    )
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Background sync for offline orders
self.addEventListener('sync', event => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

function doBackgroundSync() {
  // Handle offline orders when connection is restored
  return new Promise((resolve) => {
    // Implementation for syncing offline data
    resolve();
  });
}

// Push notification
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'Pesanan Anda sudah siap!',
    icon: '/assets/sambatanlogo.png',
    badge: '/assets/sambatanlogo.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: 'Lihat Detail',
        icon: '/assets/sambatanlogo.png'
      },
      {
        action: 'close',
        title: 'Tutup',
        icon: '/assets/sambatanlogo.png'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Sambatan Coffee', options)
  );
});

// Notification click
self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/')
    );
  }
});
