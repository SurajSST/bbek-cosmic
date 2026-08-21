import Alpine from 'alpinejs';
import { Workbox } from 'workbox-window';

// Initialize Alpine.js locally
window.Alpine = Alpine;
if (!window.AlpineStarted) {
    Alpine.start();
    window.AlpineStarted = true;
}

// Workbox Service Worker Registration
if ('serviceWorker' in navigator) {
    const wb = new Workbox('/sw.js');

    wb.addEventListener('installed', (event) => {
        if (event.isUpdate) {
            console.log('Cosmic Bill PWA: New content available, updating in background...');
        } else {
            console.log('Cosmic Bill PWA: Installed successfully for offline use.');
        }
    });

    wb.addEventListener('activated', (event) => {
        console.log('Cosmic Bill PWA: Service Worker activated and claiming clients.');
    });

    wb.register().catch((err) => {
        console.warn('Cosmic Bill PWA: Workbox registration error:', err);
    });
}
