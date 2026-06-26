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
    await expect(items.nth(0)).toHaveAttribute('href', 'https://workationcastle.com/guide/arrival/');
    await expect(items.nth(1)).toHaveAttribute('href', 'https://workationcastle.com/registration/');
    await expect(items.nth(2)).toHaveAttribute('href', 'https://workationcastle.com/guide/map/');
    await expect(items.nth(3)).toHaveAttribute('href', 'https://workationcastle.com/guide/waste-disposal/');
  });

  test('dropdown links have dark, legible text on the cream panel', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.goto('/guide/');
    // Reveal the panel (CSS :hover/:focus-within) so the link is rendered.
    await page.locator('.main-nav .has-dropdown').hover();
    const firstItem = page.locator('.main-nav .nav-dropdown a').first();
    const color = await firstItem.evaluate((el) => getComputedStyle(el).color);
    // Must be the dark panel colour (#2a2420 → rgb(42, 36, 32)), never white.
    expect(color).toBe('rgb(42, 36, 32)');
  });
});
