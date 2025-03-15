import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/filament/admin/theme.css',
                'resources/css/filament/tsn/theme.css',
                'resources/css/filament/walisantri/theme.css',
                'resources/css/app.css',
                'resources/js/app.js',
                'vendor/tomatophp/filament-simple-theme/resources/css/theme.css'
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            overlay: false,
        },
    },
});
