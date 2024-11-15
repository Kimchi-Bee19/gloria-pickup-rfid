import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svelte } from "@sveltejs/vite-plugin-svelte";
import path from 'path';

export default defineConfig({
    resolve: {
        alias: {
            'ziggy-js': path.resolve('vendor/tightenco/ziggy'),
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/svelte-app.ts',
                'resources/js/nfc-serial.ts',
            ],
            refresh: true,
        }),
        svelte(
            {
                compilerOptions: {
                    hydratable: true
                }
            }
        )
    ],
});
