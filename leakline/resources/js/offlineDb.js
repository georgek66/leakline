// This was for offline database indexedDB but failed to work
// let db;
// let readyCallbacks = [];
//
// const DB_NAME = "leakline-db";
// const DB_VERSION = 3;
// const STORE_NAME = "offline_reports";
//
// export function initDB() {
//     const request = indexedDB.open(DB_NAME, DB_VERSION);
//
//     request.onerror = () => {
//         console.error("DB failed to open");
//     };
//
//     request.onsuccess = () => {
//         db = request.result;
//         console.log("DB opened");
//
//         readyCallbacks.forEach((cb) => cb());
//         readyCallbacks = [];
//     };
//
//     request.onupgradeneeded = (event) => {
//         db = event.target.result;
//
//         if (!db.objectStoreNames.contains(STORE_NAME)) {
//             db.createObjectStore(STORE_NAME, {
//                 keyPath: "id",
//                 autoIncrement: true,
//             });
//         }
//
//         console.log("DB setup complete");
//     };
//
//
// }
// export function onDBReady(cb){
//     if (db) cb();
//     else readyCallbacks.push(cb);
// }
// // Add a report
//
// export function addOfflineReport(data) {
//     return new Promise((resolve,reject) =>
//     {
//         const tx = db.transaction(STORE_NAME, 'readwrite');
//         const store = tx.objectStore(STORE_NAME);
//
//         const request = store.add({
//             ...data,
//             created_at: new Date().toISOString(),
//             status: "pending",
//         });
//
//         request.onsuccess = () => resolve(request.result); // return new id
//         request.onerror = () => reject(request.error);
//
//         tx.onerror = () => reject(tx.error);
//
//     });
// }
// // Read offline reports
//
// export function getOfflineReports(){
//     const tx = db.transaction(STORE_NAME,"readonly");
//     const store = tx.objectStore(STORE_NAME);
//
//     const request = store.getAll();
//
//     request.onsuccess = () => {
//         console.log("Offline reports:", request.result);
//     };
//
//     request.onerror = () => {
//         console.error("Failed to read reports");
//     };
// }
//
// // Delete after sync
//
// export function deleteOfflineReport(id){
//     const tx = db.transaction(STORE_NAME, "readwrite");
//     const store = tx.objectStore(STORE_NAME);
//
//     const request = store.delete(id);
//
//     request.onsuccess = () => console.log("Offline report deleted", id);
//     request.onerror = () => console.error("Failed to delete:", request.error);
//
// }
