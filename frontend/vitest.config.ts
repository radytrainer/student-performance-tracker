import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    setupFiles: [],
    globals: true,
    css: true,
    coverage: { reporter: ['text', 'html'] }
  }
})
