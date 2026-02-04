// public/sw.js
const CACHE_NAME = "leakline-v2";
// cache pages
const PRECACHE_URLS = [
    "/offline", // fallback page shown when user is offline
    "/", // home page
    "/citizen/report", // report form page
    "/citizen/track", // track report page
];

//Css and Js are hashed when build, function to get the hashed builds
async function getViteAssetsToCache() {
    try {
        // Cache-Control: no-store -> makes sure we always read the latest manifest, not an old cached one.
        const res = await fetch("/build/manifest.json", { cache: "no-store" });
        if (!res.ok) return [];

        const manifest = await res.json();

        const entry = manifest["resources/js/app.js"];
        if (!entry) return [];

        const assets = new Set();

        //Cache the main JS file (hashed)
        if (entry.file) assets.add("/build/" + entry.file);

        //Cache the main CSS file from Vite (hashed)
        if (Array.isArray(entry.css)) {
            entry.css.forEach((c) => assets.add("/build/" + c));
        }

        // Return as an array so cache.addAll() can use it
        return [...assets];
    } catch {
        // If something fails, cache no Vite assets
        return [];
    }
}
// Download and store pages + css/js into Cache Storage
self.addEventListener("install", (event) => {
    event.waitUntil((async () => {
        //Open our cache
        const cache = await caches.open(CACHE_NAME);

        // 1) Pre-cache HTML pages
        await cache.addAll(PRECACHE_URLS);

        // 2) Pre-cache built assets (only works after npm run build)
        const viteAssets = await getViteAssetsToCache();
        await cache.addAll(viteAssets);
    })());
    // Activate the new Sw immediately instead of waiting the tab(s) to close
    self.skipWaiting();
});

// Delete old caches so users dont get old files
self.addEventListener("activate", (event) => {
    event.waitUntil((async () => {
        const keys = await caches.keys();

        await Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)));
        await self.clients.claim();
    })());
});

//Fetch runs for every request from pages controlled by SW
self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") return; // dont touch post methods
    //handle build asssets (CSS/JS)
    const url = new URL(event.request.url);

    // Cache-first for Vite build assets
    if (url.origin === self.location.origin && url.pathname.startsWith("/build/")) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE_NAME);

            // If its already cached, return it
            const cached = await cache.match(event.request);
            if (cached) return cached;

            // Otherwise fetch from network, store it and return it
            const fresh = await fetch(event.request);
            cache.put(event.request, fresh.clone());
            return fresh;
        })());

        return;
    }


    //Only handle html navigators or ignore it
    if (event.request.mode !== "navigate") return;

    // Network-first strategy for HTML pages
    // When online, user should see the newest HTML
    // If offline, use cached page or /offline fallback
    event.respondWith(
        (async () => {
            try {
                // Try to get the latest page from the internet
                const fresh = await fetch(event.request);

                // Update cache in the background
                const cache = await caches.open(CACHE_NAME);
                cache.put(event.request, fresh.clone());

                return fresh;
            } catch (err) {
                // Offline : try cashed version of the requested page
                const cached = await caches.match(event.request);

                // If not cached, show the offline fallback page
                return cached || caches.match("/offline");
            }
        })()
    );
});
