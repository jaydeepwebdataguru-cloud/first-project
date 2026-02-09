import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        proxy: {
            '/broadcasting': 'http://localhost:8001',
            '/login': 'http://localhost:8001',
            '/logout': 'http://localhost:8001',
            '/register': 'http://localhost:8001',
            '/sanctum': 'http://localhost:8001',
            '/api': 'http://localhost:8001',
        },
    },
});
