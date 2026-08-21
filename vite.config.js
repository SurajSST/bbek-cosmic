import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: null, // Registered via resources/js/app.js workbox-window
            outDir: 'public',
            filename: 'sw.js',
            strategies: 'generateSW',
            manifest: {
                id: '/',
                name: 'Cosmic Bill — SaaS Billing & Role Management',
                short_name: 'Cosmic Bill',
                description: 'Enterprise Sales Order, Invoicing, Billing Receipts, and Access Control System.',
                start_url: '/',
                scope: '/',
                display: 'standalone',
                display_override: ['window-controls-overlay', 'standalone', 'minimal-ui'],
                orientation: 'any',
                background_color: '#070a12',
                theme_color: '#4f46e5',
                lang: 'en',
                dir: 'ltr',
                categories: ['finance', 'business', 'productivity', 'utilities'],
                icons: [
                    {
                        src: '/icons/icon.svg',
                        sizes: 'any',
                        type: 'image/svg+xml',
                        purpose: 'any'
                    },
                    {
                        src: '/icons/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any'
                    },
                    {
                        src: '/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any'
                    },
                    {
                        src: '/icons/icon-maskable-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'maskable'
                    }
                ],
                shortcuts: [
                    {
                        name: 'New Sales Order',
                        short_name: 'New SO',
                        description: 'Create a new Sales Order entry',
                        url: '/admin/sales-orders/create',
                        icons: [{ src: '/icons/icon-192x192.png', sizes: '192x192' }]
                    },
                    {
                        name: 'Upload Bill',
                        short_name: 'Upload Bill',
                        description: 'Capture or upload a vendor bill receipt',
                        url: '/admin/bills/create',
                        icons: [{ src: '/icons/icon-192x192.png', sizes: '192x192' }]
                    },
                    {
                        name: 'Bulk Upload SO',
                        short_name: 'Bulk SO',
                        description: 'Upload Sales Orders via Excel/CSV spreadsheet',
                        url: '/admin/sales-orders/bulk-upload',
                        icons: [{ src: '/icons/icon-192x192.png', sizes: '192x192' }]
                    }
                ]
            },
            workbox: {
                cleanupOutdatedCaches: true,
                skipWaiting: true,
                clientsClaim: true,
                navigateFallback: 'offline.html',
                additionalManifestEntries: [
                    { url: '/offline.html', revision: '1' }
                ],
                navigateFallbackDenylist: [/^\/api\//, /^\/admin\/.*\/delete/, /^\/logout/],
                runtimeCaching: [
                    {
                        // Google Fonts stylesheets
                        urlPattern: /^https:\/\/fonts\.googleapis\.com/,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'google-fonts-stylesheets',
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    },
                    {
                        // Google Fonts webfont files
                        urlPattern: /^https:\/\/fonts\.gstatic\.com/,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-webfonts',
                            cacheableResponse: {
                                statuses: [0, 200]
                            },
                            expiration: {
                                maxEntries: 30,
                                maxAgeSeconds: 60 * 60 * 24 * 365 // 1 year
                            }
                        }
                    },
                    {
                        // Static images & icon assets
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|ico)$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'app-images-cache',
                            cacheableResponse: {
                                statuses: [0, 200]
                            },
                            expiration: {
                                maxEntries: 60,
                                maxAgeSeconds: 60 * 60 * 24 * 30 // 30 days
                            }
                        }
                    },
                    {
                        // Static JS and CSS assets
                        urlPattern: /\.(?:js|css)$/i,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'app-static-resources',
                            cacheableResponse: {
                                statuses: [0, 200]
                            }
                        }
                    }
                ]
            }
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
