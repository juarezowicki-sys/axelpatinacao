import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: './css/output.css',
        rollupOptions: {
            input: './App/src/css/input.css',
        },
    },
});