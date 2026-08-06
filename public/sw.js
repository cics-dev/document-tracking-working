const preLoad = function () {
    return caches.open("offline").then(function (cache) {
        return cache.addAll(filesToCache);
    });
};

self.addEventListener("install", function (event) {
    event.waitUntil(preLoad());
    self.skipWaiting();
});

self.addEventListener("activate", function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.map(function (key) {
                if (key !== "offline") {
                    return caches.delete(key);
                }
            }));
        })
    );
    self.clients.claim();
});

const filesToCache = [
    '/',
    '/offline.html'
];

const addToCache = function (request) {
    if (!request.url.startsWith('http')) {
        return Promise.resolve();
    }

    if (request.mode === "navigate" || request.destination === "document") {
        return Promise.resolve();
    }

    return caches.open("offline").then(function (cache) {
        return fetch(request).then(function (response) {
            if (response.ok && request.destination !== "document") {
                cache.put(request, response.clone());
            }
            return response;
        });
    });
};

self.addEventListener("fetch", function (event) {
    const request = event.request;

    if (request.method !== "GET") {
        return;
    }

    if (request.mode === "navigate" || request.destination === "document") {
        event.respondWith(
            fetch(request).then(function (response) {
                return response;
            }).catch(function () {
                return caches.open("offline").then(function (cache) {
                    return cache.match("/offline.html");
                });
            })
        );
        return;
    }

    event.respondWith(
        caches.match(request).then(function (cached) {
            if (cached) {
                return cached;
            }

            return fetch(request).then(function (response) {
                if (response.ok) {
                    return caches.open("offline").then(function (cache) {
                        cache.put(request, response.clone());
                        return response;
                    });
                }

                return response;
            }).catch(function () {
                return caches.open("offline").then(function (cache) {
                    return cache.match("/offline.html");
                });
            });
        })
    );

    if (!request.url.startsWith('http')) {
        event.waitUntil(addToCache(request));
    }
});
