export async function handleFetch(event) {
    if (event.request.url.includes('UserController.php')) {
        event.respondWith(
            fetch(event.request).catch(error => {
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

    if (event.request.method === 'GET') {
        event.respondWith(
            caches.match(event.request)
                .then(response => response || fetchAndCache(event.request))
                .catch(() => caches.match('./offline.html'))
        );
    } else {
        event.respondWith(fetch(event.request));
    }
}

async function fetchAndCache(request) {
    const response = await fetch(request);
    if (!response || response.status !== 200 || response.type !== 'basic') return response;

    const responseToCache = response.clone();
    const cache = await caches.open(CACHE_NAME);
    cache.put(request, responseToCache);
    return response;
}
