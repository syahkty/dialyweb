self.addEventListener("install", event => {
    event.waitUntil(
      caches.open("tuku-cache-v1").then(cache => {
        return cache.addAll([
          "/index.php",
          "/login.php",
          "/profil.php",
          "/schedule.php",
          "https://syahkty.web.id/gambar/favicon.png"
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
  