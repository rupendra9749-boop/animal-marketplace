import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Vite's default target emits syntax (e.g. ||=, ??=) that WebViews older than Chrome 85
        // cannot parse, which silently kills all JavaScript (menus, dropdowns) in the Android app.
        target: ['es2019', 'chrome70', 'safari12'],
    },
});
