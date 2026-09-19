import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon.ico', 'icons/*.png'],
            manifest: false,
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                runtimeCaching: [
                    { urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/, handler: 'CacheFirst', options: { cacheName: 'google-fonts', expiration: { maxEntries: 20, maxAgeSeconds: 2592000 } } },
                    { urlPattern: /\/api\/availability.*/, handler: 'NetworkFirst', options: { cacheName: 'api-availability', networkTimeoutSeconds: 3, expiration: { maxEntries: 50, maxAgeSeconds: 300 } } },
                    { urlPattern: /\/api\/check-availability.*/, handler: 'NetworkOnly' },
                ],
            },
        }),
    ],
});
