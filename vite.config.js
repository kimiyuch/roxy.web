import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // InterVariable from rsms.me, cut down to latin and weights 400–600 (same file as croc.web).
                local('Inter', {
                    variants: [{ src: 'resources/fonts/InterVariable.woff2', weight: '400 600' }],
                    variable: '--font-inter',
                }),
            ],
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: { base: null, includeAbsolute: false },
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
