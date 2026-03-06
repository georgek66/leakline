// resources/js/reportSync.js

const DB_NAME    = 'leakline-db';
const DB_VERSION = 1;
const STORE      = 'pending-reports';

// Open (or create) the IndexedDB database
function openDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);

        // Runs only first time (or when DB_VERSION increases)
        req.onupgradeneeded = (e) => {
            e.target.result.createObjectStore(STORE, {
                keyPath: 'id',
                autoIncrement: true,
            });
        };

        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });
}

// Save a report to IndexedDB
export async function saveReportOffline(formEl) {
    const data = new FormData(formEl);

    // Build a plain object (FormData can't be stored in IndexedDB)
    const report = {
        client_id:     crypto.randomUUID(), // unique ID generated on device
        category_id:   data.get('category_id'),
        severity_id:   data.get('severity_id'),
        latitude:      data.get('latitude'),
        longitude:     data.get('longitude'),
        location:      data.get('location') || `${data.get('latitude')}, ${data.get('longitude')}`,
        description:   data.get('description'),
        contact_name:  data.get('contact_name'),
        contact_email: data.get('contact_email'),
        contact_phone: data.get('contact_phone'),
        consent:       data.get('consent') ? 1 : 0,
        saved_at:      new Date().toISOString(),
    };
    // Validate consent
    const hasContact = report.contact_name || report.contact_email || report.contact_phone;
    if (hasContact && !report.consent) {
        throw new Error('Please accept consent if you provide contact details.');
    }

    // Save to IndexedDB
    const db = await openDB();
    await new Promise((resolve, reject) => {
        const tx  = db.transaction(STORE, 'readwrite');
        const req = tx.objectStore(STORE).add(report);
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });

    // Tell the SW: "sync when you're online"
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        const sw = await navigator.serviceWorker.ready;
        await sw.sync.register('sync-reports');
    }

    return report.client_id; // return so we can show the user something
}
const reportForm = document.getElementById('leakline_report');

if (reportForm) {
    reportForm.addEventListener('submit', async function (e) {

        if (navigator.onLine) return; // if online then submit normally

        e.preventDefault(); // if offline then intercept the post request

        try {
            await saveReportOffline(this);
            alert(' You\'re offline! Your report is saved and will be sent automatically when you reconnect.');
            this.reset();
        } catch (err) {
            console.error('Failed to save offline:', err);
            alert(err.message || 'Something went wrong. Please try again.');
        }
    });
}
