import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
  const isProduction = mode === 'production';

  return {
    plugins: [vue()],
    base: isProduction ? './' : '/',
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      },
    },
    build: {
      outDir: 'dist',
      emptyOutDir: true,
      rollupOptions: {
        input: path.resolve(__dirname, 'src/main.js'),
        output: {
          assetFileNames: 'assets/[name][extname]',
          entryFileNames: 'assets/main.js',
          chunkFileNames: 'assets/[name].js',
        },
      },
    },
    server: {
      port: 5174,
      strictPort: true,
      proxy: {
        '/wp-admin': 'http://localhost/giantwpsolutions',
        '/wp-content': 'http://localhost/giantwpsolutions',
      },
    },
  };
});
