import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/sage.css', 'resources/css/editorial.css', 'resources/js/app.jsx', 'resources/js/editor.jsx', 'resources/js/public.js', 'resources/js/manage.js'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
});
