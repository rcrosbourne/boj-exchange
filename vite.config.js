import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            protocol: 'wss',
            host: 'boj-exchange.ddev.site',
        },
        host: '0.0.0.0',
        strictPort: true,
        // dev server port
        port: 3000,
        // SSR server port
        // port: 13714,
    },
});
