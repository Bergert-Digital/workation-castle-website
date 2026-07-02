import { test, expect } from '@playwright/test';

test.describe('Ways to Stay', () => {
  test('landing page renders hero and three way cards', async ({ page }) => {
    await page.goto('/ways-to-stay/');
    await expect(page.locator('.page-hero h1')).toHaveText('Find the stay that fits');
    const cards = page.locator('.ways-grid .way');
    await expect(cards).toHaveCount(3);
    await expect(cards.nth(0)).toContainText('Team retreats');
    await expect(cards.nth(1)).toContainText('Workations');
    await expect(cards.nth(2)).toContainText('Family & group stays');
  });

  test('cards band is full-bleed, not squeezed to content width', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 1100 });
    await page.goto('/ways-to-stay/');
    // The band background must span the whole viewport (alignfull), flush to the
    // left edge — not capped at the 720px content width like a plain block.
    const box = await page.locator('.band-deep').boundingBox();
    expect(box).not.toBeNull();
    expect(box!.width).toBeGreaterThanOrEqual(1430);
    expect(box!.x).toBeLessThanOrEqual(2);
    // The cards inside stay centered (wider than content width, well over 720px).
    const grid = await page.locator('.ways-grid').boundingBox();
    expect(grid!.width).toBeGreaterThan(900);
  });

  test('cards link to the three sub-pages', async ({ page }) => {
    await page.goto('/ways-to-stay/');
    const links = page.locator('.ways-grid .way a.link');
    await expect(links.nth(0)).toHaveAttribute('href', '/ways-to-stay/team-retreats/');
    await expect(links.nth(1)).toHaveAttribute('href', '/ways-to-stay/workations/');
    await expect(links.nth(2)).toHaveAttribute('href', '/ways-to-stay/family-and-groups/');
  });

  test('each sub-page resolves and renders its hero', async ({ page }) => {
    const subpages = [
      { url: '/ways-to-stay/team-retreats/', heading: 'Team retreats' },
      { url: '/ways-to-stay/workations/', heading: 'Workations' },
      { url: '/ways-to-stay/family-and-groups/', heading: 'Family & group stays' },
    ];
    for (const { url, heading } of subpages) {
      const response = await page.goto(url);
      expect(response?.status(), `${url} should resolve`).toBe(200);
      await expect(page.locator('.page-hero h1')).toHaveText(heading);
    }
  });

  test('sub-pages are full SEO landing pages, not stubs', async ({ page }) => {
    const subpages = [
      '/ways-to-stay/team-retreats/',
      '/ways-to-stay/workations/',
      '/ways-to-stay/family-and-groups/',
    ];
    for (const url of subpages) {
      await page.goto(url);
      // Two image+text feature rows.
      await expect(page.locator('.space-row')).toHaveCount(2);
      // A testimonials band with three reviews.
      await expect(page.locator('.reviews-grid > *')).toHaveCount(3);
      // Substantial body copy (several paragraphs of SEO content).
      expect(await page.locator('.entry-content p').count()).toBeGreaterThanOrEqual(8);
      // The homepage-style closing CTA with its night-image background.
      const bg = page.locator('.closing img.bg');
      await expect(bg).toHaveAttribute('src', /\.jpe?g/i);
      await expect(page.locator('.closing .wc-btn')).toHaveCount(2);
    }
  });

  test('homepage cards point at the Ways to Stay sub-pages', async ({ page }) => {
    await page.goto('/');
    const links = page.locator('.ways-grid .way a.link');
    await expect(links.nth(0)).toHaveAttribute('href', '/ways-to-stay/team-retreats/');
    await expect(links.nth(1)).toHaveAttribute('href', '/ways-to-stay/workations/');
    await expect(links.nth(2)).toHaveAttribute('href', '/ways-to-stay/family-and-groups/');
  });

  test('header has a Ways to stay dropdown with three sub-page links', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 900 });
    await page.goto('/ways-to-stay/');
    const dropdown = page
      .locator('.site-header .wp-block-navigation-submenu')
      .filter({ hasText: 'Ways to stay' });
    const trigger = dropdown.locator('> a.wp-block-navigation-item__content');
    await expect(trigger).toHaveAttribute('href', '/ways-to-stay/');
    await expect(trigger).toContainText('Ways to stay');
    const items = dropdown.locator('.wp-block-navigation__submenu-container a');
    await expect(items).toHaveCount(3);
    await expect(items.nth(0)).toHaveAttribute('href', '/ways-to-stay/team-retreats/');
    await expect(items.nth(1)).toHaveAttribute('href', '/ways-to-stay/workations/');
    await expect(items.nth(2)).toHaveAttribute('href', '/ways-to-stay/family-and-groups/');
  });
});
