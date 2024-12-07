// Cache name - add version for easier updates
const CACHE_NAME = 'static-cache-v1';

// Resources to cache
const RESOURCES_TO_CACHE = [
    './',
    '.assets/img/SanAndres.jpg',
    './assets/css/bootstrap.min.css',
    './assets/dist/js/bootstrap.min.js',
    './assets/dist/jquery.min.js',
    './assets/dist/popper.min.js',
    './assets/dist/sweetalert.min.js',
];

// Install event handler
self.addEventListener('install', event => {
    console.log('Service Worker installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Opened cache');
                return cache.addAll(RESOURCES_TO_CACHE)
                    .then(() => {
                        console.log('All resources cached successfully');
                    })
                    .catch(error => {
                        console.error('Failed to cache resources:', error);
                        return Promise.all(
                            RESOURCES_TO_CACHE.map(url => {
                                return cache.add(url).catch(err => {
                                    console.error(`Failed to cache ${url}:`, err);
                                });
                            })
                        );
                    });
            })
    );
});

// Activate event handler
self.addEventListener('activate', event => {
    console.log('Service Worker activating...');
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

// Fetch event handler
self.addEventListener('fetch', event => {
    // Check if the request is for the UserController
    if (event.request.url.includes('UserController.php')) {
        // For UserController requests, always go to network
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    return response;
                })
                .catch(error => {
                    console.error('Failed to fetch from UserController:', error);
                    return new Response(JSON.stringify({
                        error: 'Network error occurred',
                        offline: true
                    }), {
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
        return;
    }

    // For all other requests
    if (event.request.method === 'GET') {
        event.respondWith(
            caches.match(event.request)
                .then(response => {
                    if (response) {
                        console.log('Found in cache:', event.request.url);
                        return response;
                    }

                    const fetchRequest = event.request.clone();

                    return fetch(fetchRequest)
                        .then(response => {
                            if (!response || response.status !== 200 || response.type !== 'basic') {
                                return response;
                            }

                            const responseToCache = response.clone();

                            caches.open(CACHE_NAME)
                                .then(cache => {
                                    console.log('Caching new resource:', event.request.url);
                                    cache.put(event.request, responseToCache);
                                });

                            return response;
                        })
                        .catch(error => {
                            console.error('Fetch failed:', error);
                            return caches.match('./offline.html');
                        });
                })
        );
    } else {
        // For non-GET requests (POST, PUT, DELETE, etc.)
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    return response;
                })
                .catch(error => {
                    console.error('Non-GET request failed:', error);
                    return new Response(JSON.stringify({
                        error: 'Network error occurred',
                        offline: true
                    }), {
                        headers: { 'Content-Type': 'application/json' }
                    });
                })
        );
    }
});

// Handle service worker updates
self.addEventListener('message', event => {
    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }
});