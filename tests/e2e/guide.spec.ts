import { test, expect } from '@playwright/test';
import { seedConsent } from './utils';

test.describe('Guest Guide', () => {
  test.beforeEach(async ({ page }) => {
    await seedConsent(page);
  });

  test('guide page renders hero and four topic cards', async ({ page }) => {
    await page.goto('/guide/');
    await expect(page.locator('.page-hero h1')).toHaveText('Everything for your stay');
    const cards = page.locator('.starter-feature-grid .starter-feature');
    await expect(cards).toHaveCount(4);
    await expect(cards.nth(0)).toContainText('How to get here');
    await expect(cards.nth(3)).toContainText('Sorting the waste');
  });

  test('card icons are solid amber tiles with a dark glyph', async ({ page }) => {
    await page.goto('/guide/');
    const tile = page.locator('.guide-cards .starter-feature__ic').first();
    const { bg, glyph } = await tile.evaluate((el) => ({
      bg: getComputedStyle(el).backgroundColor,
      glyph: getComputedStyle(el.querySelector('svg')!).color,
    }));
    // Solid accent amber tile (not the light accent-tint), dark ink glyph.
    expect(bg).toBe('rgb(254, 198, 1)');
    expect(glyph).toBe('rgb(36, 28, 18)');
  });

  test('cards are width-constrained and centered, not full-bleed', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1100 });
    await page.goto('/guide/');
    // The grid is align:wide inside a native constrained group, so it caps at
    // theme.json's wideSize (1200px) — driven by the layout system, not a
    // hardcoded width — instead of bleeding edge-to-edge.
    const box = await page.locator('.guide-cards').boundingBox();
    expect(box).not.toBeNull();
    // ~wideSize 1200px, well below the 1440px viewport...
    expect(box!.width).toBeLessThanOrEqual(1210);
    expect(box!.width).toBeGreaterThan(1000);
    // ...and centered (meaningful left/right gutters, not touching the edge).
    expect(box!.x).toBeGreaterThan(40);
  });

  test('cards have generous vertical padding (not cramped against hero/footer)', async ({ page }) => {
    await page.goto('/guide/');
    const grid = page.locator('.guide-cards');
    const { top, bottom } = await grid.evaluate((el) => {
      const cs = getComputedStyle(el);
      return { top: parseFloat(cs.paddingTop), bottom: parseFloat(cs.paddingBottom) };
    });
    expect(top).toBeGreaterThanOrEqual(48);
    expect(bottom).toBeGreaterThanOrEqual(48);
  });

  test('topic links point at the live sub-pages', async ({ page }) => {
    await page.goto('/guide/');
    const links = page.locator('.starter-feature-grid .starter-feature__more');
    await expect(links.nth(0)).toHaveAttribute('href', '/guide/arrival/');
    await expect(links.nth(1)).toHaveAttribute('href', '/check-in/');
    await expect(links.nth(2)).toHaveAttribute('href', 'https://workationcastle.com/guide/map/');
    await expect(links.nth(3)).toHaveAttribute('href', 'https://workationcastle.com/guide/waste-disposal/');
  });

  test('whole card is the click target, with no visible "Open" button', async ({ page }) => {
    await page.goto('/guide/');
    // No visible "Open" call-to-action anywhere in the grid.
    await expect(page.locator('.guide-cards')).not.toContainText('Open');
    const card = page.locator('.guide-cards .starter-feature').first();
    const link = card.locator('.starter-feature__more');
    // The link has a distinct, descriptive accessible name (not "Open").
    await expect(link).toHaveAccessibleName('How to get here');
    // The link is stretched to overlay (essentially) the whole card.
    const cardBox = await card.boundingBox();
    const linkBox = await link.boundingBox();
    expect(linkBox).not.toBeNull();
    expect(linkBox!.width).toBeGreaterThan(cardBox!.width - 4);
    expect(linkBox!.height).toBeGreaterThan(cardBox!.height - 4);
  });

  test('header has a Guest Guide dropdown with four items', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.goto('/guide/');
    const guideDropdown = page
      .locator('.site-header .wp-block-navigation-submenu')
      .filter({ hasText: 'Guest Guide' });
    const trigger = guideDropdown.locator('> a.wp-block-navigation-item__content');
    await expect(trigger).toHaveAttribute('href', '/guide/');
    await expect(trigger).toContainText('Guest Guide');
    const items = guideDropdown.locator('.wp-block-navigation__submenu-container a');
    await expect(items).toHaveCount(4);
    await expect(items.nth(0)).toHaveAttribute('href', '/guide/arrival/');
    await expect(items.nth(1)).toHaveAttribute('href', '/check-in/');
    await expect(items.nth(2)).toHaveAttribute('href', 'https://workationcastle.com/guide/map/');
    await expect(items.nth(3)).toHaveAttribute('href', 'https://workationcastle.com/guide/waste-disposal/');
  });

  test('dropdown links have dark, legible text on the cream panel', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.goto('/guide/');
    const guideDropdown = page
      .locator('.site-header .wp-block-navigation-submenu')
      .filter({ hasText: 'Guest Guide' });
    // Reveal the panel (CSS :hover/:focus-within) so the link is rendered.
    await guideDropdown.hover();
    const firstItem = guideDropdown.locator('.wp-block-navigation__submenu-container a').first();
    const color = await firstItem.evaluate((el) => getComputedStyle(el).color);
    // Must be the dark panel colour (--wc-ink #241C12 → rgb(36, 28, 18)), never white.
    expect(color).toBe('rgb(36, 28, 18)');
  });
});
