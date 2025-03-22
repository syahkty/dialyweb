self.addEventListener("install", event => {
    event.waitUntil(
      caches.open("tuku-cache-v1").then(cache => {
        return cache.addAll([
          "/index.php",
        ]);
      })
    );
  });
  
  self.addEventListener("fetch", event => {
    event.respondWith(
      caches.match(event.request).then(response => {
        return response || fetch(event.request);
      })
    );
  });
  