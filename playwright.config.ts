import { readFileSync } from 'node:fs';
import { defineConfig, devices } from '@playwright/test';

// Match wp-env's port: `.wp-env.override.json` (per-workspace, gitignored) wins,
// falling back to the `.wp-env.json` default. `WP_BASE_URL` overrides both.
function resolveBaseURL(): string {
  if (process.env.WP_BASE_URL) return process.env.WP_BASE_URL;
  for (const file of ['.wp-env.override.json', '.wp-env.json']) {
    try {
      const port = JSON.parse(readFileSync(new URL(file, import.meta.url), 'utf8')).port;
      if (port) return `http://localhost:${port}`;
    } catch {
      // File absent or portless — try the next source.
    }
  }
  return 'http://localhost:8890';
}

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: 'html',
  use: {
    baseURL: resolveBaseURL(),
    trace: 'on-first-retry',
  },
  projects: [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
  ],
});
