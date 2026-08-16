import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { resolve } from 'path'

/**
 * Wraps the final bundle in an IIFE to prevent top-level variables
 * from leaking into the global scope. This is necessary because
 * WordPress loads the script via a regular <script> tag (not a module),
 * and terser's minified variable names (like `_`) would otherwise
 * overwrite globals such as Underscore.js, breaking wp-admin scripts.
 */
function iifeWrapPlugin() {
    return {
        name: 'iife-wrap',
        enforce: 'post',
        generateBundle(options, bundle) {
            for (const [fileName, chunk] of Object.entries(bundle)) {
                if (chunk.type === 'chunk' && fileName.endsWith('.js')) {
                    chunk.code = `(function(){${chunk.code}})();\n`
                }
            }
        },
    }
}

export default defineConfig({
    plugins: [
        vue(),
        tailwindcss(),
        iifeWrapPlugin(),
    ],

    css: {
        preprocessorOptions: {
            scss: {
                api: 'modern-compiler',
            },
        },
    },

    build: {
        minify: 'terser',
        terserOptions: {
            mangle: {
                reserved: ['wp', 'Wp', 'jQuery', '$']
            }
        },
        manifest: true,
        outDir: 'assets/admin',
        assetsDir: '',
        emptyOutDir: true,

        rolldownOptions: {
            input: 'admin/main.js',
            output: {
                entryFileNames: 'scripts.js',
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'styles.css'
                    }
                    return '[name][extname]'
                },
            },
        },
    },

    resolve: {
        alias: {
            '@': resolve(__dirname, 'admin'),
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
        dedupe: ['vue', 'vue-router', 'pinia', '@vuepic/vue-datepicker'],
    }
})
