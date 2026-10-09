import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

// In Docker, VITE_API_PROXY_TARGET points at the backend service name
// (e.g. http://backend:8000) so the Vite dev server - not the browser -
// talks to the backend container directly over the compose network.
// Outside Docker it defaults to localhost.
const proxyTarget = process.env.VITE_API_PROXY_TARGET || 'http://localhost:8000';

export default defineConfig({
  plugins: [react()],
  server: {
    host: true,
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': {
        target: proxyTarget,
        changeOrigin: true,
      },
    },
  },
});
