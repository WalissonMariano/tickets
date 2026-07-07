import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/layouts/menu.css', 'resources/css/components/home.css', 'resources/css/components/dashboard.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
