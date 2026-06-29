import { test, expect } from '@playwright/test';

const CANYON = '/activities/canyon-tour-in-val-sanagra/';

test.describe('Activity map consent gating', () => {
  test('no OpenStreetMap tiles load until functional consent', async ({ page }) => {
    const tileReqs: string[] = [];
    page.on('request', (r) => {
      if (/tile\.openstreetmap\.org/.test(r.url())) tileReqs.push(r.url());
    });
    await page.goto(CANYON);
    // Modal is blocking; reject all.
    await page.locator('.wc-consent-modal__reject-all').click();
    await page.waitForTimeout(500);
    expect(tileReqs).toEqual([]);
  });

  test('accepting functional initializes the map (tiles load)', async ({ page }) => {
    const tileReqs: string[] = [];
    page.on('request', (r) => {
      if (/tile\.openstreetmap\.org/.test(r.url())) tileReqs.push(r.url());
    });
    await page.goto(CANYON);
    await page.locator('.wc-consent-modal__accept-all').click();
    await page.waitForLoadState('networkidle');
    expect(tileReqs.length).toBeGreaterThan(0);
    // Leaflet marks the container once initialized.
    await expect(page.locator('.wc-activity-map.leaflet-container')).toHaveCount(1);
  });
});
