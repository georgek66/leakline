// public/sw.js
const CACHE_NAME = "leakline-v4";

// Pages to precache (must be reachable without auth redirects)
const PRECACHE_URLS = [
    "/offline",
    "/",
    "/citizen/report",
    "/citizen/track",
];

function openLeakLineDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open("leakline-db", 1);

        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore("pending-reports", {
                keyPath: "id",
                autoIncrement: true,
            });
        };

        req.onsuccess = (e) => resolve({
            getAll: (store) => new Promise((res, rej) => {
                const tx = e.target.result.transaction(store, "readonly");
                const req = tx.objectStore(store).getAll();
                req.onsuccess = () => res(req.result);
                req.onerror = () => rej(req.error);
            }),
            delete: (store, key) => new Promise((res, rej) => {
                const tx = e.target.result.transaction(store, "readwrite");
                const req = tx.objectStore(store).delete(key);
                req.onsuccess = () => res();
                req.onerror = () => rej(req.error);
            }),
        });

        req.onerror = () => reject(req.error);
    });
}



// Css and Js are hashed when build
async function getViteAssetsToCache() {
    try {
        const res = await fetch("/build/manifest.json", { cache: "no-store" });
        if (!res.ok) return [];

        const manifest = await res.json();
        const entry = manifest["resources/js/app.js"];
        if (!entry) return [];

        const assets = new Set();
        if (entry.file) assets.add("/build/" + entry.file);

        if (Array.isArray(entry.css)) {
            entry.css.forEach((c) => assets.add("/build/" + c));
        }

        return [...assets];
    } catch (e) {
        console.log("[SW] manifest fetch failed (dev mode is OK)", e);
        return [];
    }
}

// Try pre-cache without failing the whole install
async function safeAddAll(cache, urls) {
    const results = await Promise.allSettled(
        urls.map(async (u) => {
            // cache: "reload" is a common MDN approach during install to avoid stale responses
            const req = new Request(u, { cache: "reload" });
            const res = await fetch(req);

            if (!res.ok) throw new Error(`${u} -> HTTP ${res.status}`);
            await cache.put(req, res);
            return u;
        })
    );

    const ok = results.filter(r => r.status === "fulfilled").length;
    const bad = results
        .filter(r => r.status === "rejected")
        .map(r => r.reason?.message || String(r.reason));

    console.log("[SW] precache results", { ok, failed: bad });
}

self.addEventListener("install", (event) => {
    console.log("[SW] install start", { CACHE_NAME });

    event.waitUntil((async () => {
        const cache = await caches.open(CACHE_NAME);

        // 1) Pre-cache HTML pages (non-fatal)
        await safeAddAll(cache, PRECACHE_URLS);

        // 2) Pre-cache built assets (only after npm run build)
        const viteAssets = await getViteAssetsToCache();
        if (viteAssets.length) {
            await safeAddAll(cache, viteAssets);
        } else {
            console.log("[SW] no vite assets to precache (likely npm run dev)");
        }

        console.log("[SW] install done");
    })());

    self.skipWaiting();
});

self.addEventListener("activate", (event) => {
    console.log("[SW] activate start");

    event.waitUntil((async () => {
        const keys = await caches.keys();
        const oldKeys = keys.filter((k) => k !== CACHE_NAME);

        await Promise.all(oldKeys.map((k) => caches.delete(k)));
        await self.clients.claim();

        console.log("[SW] activate done", { deleted: oldKeys });
    })());
});

self.addEventListener("fetch", (event) => {
    // MDN-style: fetch fires for requests from controlled pages
    // console.log("[SW] fetch", event.request.method, event.request.mode, event.request.url);

    if (event.request.method !== "GET") return;


    const url = new URL(event.request.url);

    // Cache-first for Vite build assets
    if (url.origin === self.location.origin && url.pathname.startsWith("/build/")) {
        event.respondWith((async () => {
            const cache = await caches.open(CACHE_NAME);
            const cached = await cache.match(event.request);
            if (cached) return cached;

            const fresh = await fetch(event.request);
            cache.put(event.request, fresh.clone());
            return fresh;
        })());
        return;
    }

    // Only handle full page navigations
    if (event.request.mode !== "navigate") return;

    // Network-first for HTML
    event.respondWith((async () => {
        try {
            const fresh = await fetch(event.request);
            const cache = await caches.open(CACHE_NAME);
            cache.put(event.request, fresh.clone());
            return fresh;
        } catch (err) {
            const cached = await caches.match(event.request);
            return cached || caches.match("/offline");
        }
    })());

});

self.addEventListener("sync", (event) => {
    console.log("[SW] sync event fired, tag:", event.tag);

    if (event.tag === "sync-reports") {
        event.waitUntil(syncPendingReports());
    }
});
async function syncPendingReports() {
    const db = await openLeakLineDB();
    const pending = await db.getAll("pending-reports");

    console.log("[SW] pending reports:", pending);

    for (const report of pending) {
        console.log("[SW] sending report:", report.client_id);
        try {
            const response = await fetch("/citizen/report/sync", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(report),
            });


            // check if http status is 200
            if (response.ok) {

                const data = await response.json();
                const ticketId = data.ticket_id;
                console.log("[SW] sync success! ticket:", ticketId);
                await db.delete("pending-reports", report.id);
                // Only notify when permission granted
                try {
                    await self.registration.showNotification("LeakLine", {
                        body: `Your report was submitted successfully! Ticket ID: ${ticketId}`,
                        tag: ticketId, // prevent duplicate notifications
                        data: {
                            url: `/citizen/received/${ticketId}` // clickable
                        }
                    });
                } catch (err) {
                    console.log("[SW] notification skipped:", err.message);
                }
            }
        } catch (err) {
            console.error("Sync failed, will retry:", err);
        }
    }

}
// When user clicks the notification it opens the ticket page
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url;

    if (url) {
        event.waitUntil(
            clients.openWindow(url)
        );
    }
});
