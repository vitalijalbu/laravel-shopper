import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    build: {
        lib: {
            entry: resolve(__dirname, 'resources/js/app.js'),
            name: 'ShopperAdmin',
            formats: ['es', 'umd'],
            fileName: (format) => `shopper-admin.${format}.js`,
        },
        rollupOptions: {
            external: ['vue', 'axios'],
            output: {
                globals: {
                    vue: 'Vue',
                    axios: 'axios',
                },
            },
        },
        outDir: 'public',
        emptyOutDir: false,
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
            '~': resolve(__dirname, 'resources'),
        },
    },
    define: {
        __VUE_OPTIONS_API__: true,
        __VUE_PROD_DEVTOOLS__: false,
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
    },
});
