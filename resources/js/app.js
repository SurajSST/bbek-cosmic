import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { Workbox } from 'workbox-window';

const appName = import.meta.env.VITE_APP_NAME || 'Cosmic Bill';

createInertiaApp({
    title: (title) => title ? `${title} — ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.component('Head', Head);
        app.component('Link', Link);
        app.mount(el);
    },
    progress: {
        color: '#6366f1',
        showSpinner: true,
    },
});

// PWA Workbox Service Worker Registration
if ('serviceWorker' in navigator) {
    const wb = new Workbox('/sw.js');

    wb.addEventListener('installed', (event) => {
        if (event.isUpdate) {
            console.log('Cosmic Bill PWA: New version available, updated in background.');
        } else {
            console.log('Cosmic Bill PWA: Offline service worker active.');
        }
    });

    wb.register().catch((err) => {
        console.warn('Cosmic Bill PWA: Service Worker registration error:', err);
    });
}
