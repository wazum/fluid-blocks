import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: './scenarios',
  forbidOnly: !!process.env.CI,
  workers: 1,
  reporter: process.env.CI ? [['list'], ['github']] : 'list',
  use: {
    baseURL: `http://127.0.0.1:${process.env.E2E_PORT ?? '8080'}`,
    trace: 'retain-on-failure',
  },
  projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],
})
