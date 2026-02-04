import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            const reg = await navigator.serviceWorker.register("/sw.js");
            console.log("Service Worker registered", reg.scope);
        } catch (err) {
            console.error("Service Worker register failed", err);
        }
    });
}

