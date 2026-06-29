import { test, expect } from '@playwright/test';

test.describe('Self-hosted fonts', () => {
  test('homepage makes no request to Google Fonts', async ({ page }) => {
    const googleFontReqs: string[] = [];
    page.on('request', (r) => {
      const u = r.url();
      if (u.includes('fonts.googleapis.com') || u.includes('fonts.gstatic.com')) {
        googleFontReqs.push(u);
      }
    });
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    expect(googleFontReqs).toEqual([]);
  });

  test('a self-hosted Inria woff2 is requested', async ({ page }) => {
    const fontReqs: string[] = [];
    page.on('request', (r) => {
      if (/assets\/fonts\/inria-.*\.woff2/.test(r.url())) fontReqs.push(r.url());
    });
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    expect(fontReqs.length).toBeGreaterThan(0);
  });
});
