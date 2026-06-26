import { test, expect } from '@playwright/test';

test.describe('Guest Guide', () => {
  test('guide page renders hero and four topic cards', async ({ page }) => {
    await page.goto('/guide/');
    await expect(page.locator('.page-hero h1')).toHaveText('Everything for your stay');
    const cards = page.locator('.starter-feature-grid .starter-feature');
    await expect(cards).toHaveCount(4);
    await expect(cards.nth(0)).toContainText('How to get here');
    await expect(cards.nth(3)).toContainText('Sorting the waste');
  });

  test('topic links point at the live sub-pages', async ({ page }) => {
    await page.goto('/guide/');
    const links = page.locator('.starter-feature-grid .starter-feature__more');
    await expect(links.nth(0)).toHaveAttribute('href', 'https://workationcastle.com/guide/arrival/');
    await expect(links.nth(1)).toHaveAttribute('href', 'https://workationcastle.com/registration/');
    await expect(links.nth(2)).toHaveAttribute('href', 'https://workationcastle.com/guide/map/');
    await expect(links.nth(3)).toHaveAttribute('href', 'https://workationcastle.com/guide/waste-disposal/');
  });

  test('header has a Guest Guide dropdown with four items', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.goto('/guide/');
    const trigger = page.locator('.main-nav .has-dropdown > a.nav-dropdown-trigger');
    await expect(trigger).toHaveAttribute('href', '/guide/');
    await expect(trigger).toContainText('Guest Guide');
    const items = page.locator('.main-nav .nav-dropdown a');
    await expect(items).toHaveCount(4);
  });
});
