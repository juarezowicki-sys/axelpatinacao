import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: './public_html/css/output.css',
        rollupOptions: {
            input: './projeto/src/App/src/css/input.css',
        },
    },
});