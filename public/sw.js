// Makes AnimalMandi installable from the browser ("Install app" / "Add to Home screen").
// It caches nothing: every page still comes straight from the site.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
