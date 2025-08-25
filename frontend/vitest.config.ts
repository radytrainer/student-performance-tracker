import { defineConfig } from 'vitest/config'
import { mergeConfig } from 'vite'
import viteConfig from './vite.config.js'

export default mergeConfig(viteConfig, defineConfig({
  test: {
    environment: 'jsdom',
    setupFiles: [],
    globals: true,
    css: true,
    coverage: { reporter: ['text', 'html'] }
  }
}))
