import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
<<<<<<< HEAD
=======
import tailwindcss from '@tailwindcss/vite';
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
<<<<<<< HEAD
    ],
=======
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
});
