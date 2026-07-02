import { test, expect } from '@playwright/test';
import { seedConsent } from './utils';

test.describe('mobile menu', () => {
  // A phone-sized viewport, below the 900px breakpoint where the hamburger
  // replaces the horizontal nav.
  test.use({ viewport: { width: 390, height: 844 } });

  test.beforeEach(async ({ page }) => {
    await seedConsent(page);
  });

  test('hamburger toggles the primary navigation', async ({ page }) => {
    await page.goto('/');

    const toggle = page.locator('.menu-toggle');
    const nav = page.locator('nav.main-nav');

    // Closed by default: toggle visible, nav hidden, state reflected in ARIA.
    await expect(toggle).toBeVisible();
    await expect(nav).toBeHidden();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');

    // Opening reveals the nav and its links.
    await toggle.click();
    await expect(nav).toBeVisible();
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(nav.getByRole('link', { name: 'Activities' })).toBeVisible();
    await expect(nav.getByRole('link', { name: 'Photos' })).toBeVisible();

    // Dropdown sub-links must be reachable on touch (no hover), so they are
    // expanded inline within the open panel.
    await expect(nav.getByRole('link', { name: 'Team retreats' })).toBeVisible();
    await expect(nav.getByRole('link', { name: 'How to get here' })).toBeVisible();

    // Toggling again closes it.
    await toggle.click();
    await expect(nav).toBeHidden();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  });

  test('menu closes when a navigation link is tapped', async ({ page }) => {
    await page.goto('/');
    const toggle = page.locator('.menu-toggle');
    const nav = page.locator('nav.main-nav');

    await toggle.click();
    await expect(nav).toBeVisible();

    await nav.getByRole('link', { name: 'Activities' }).click();
    // Navigated away; on the new page the menu starts closed.
    await expect(page.locator('nav.main-nav')).toBeHidden();
    await expect(page.locator('.menu-toggle')).toHaveAttribute('aria-expanded', 'false');
  });

  test('menu closes on Escape', async ({ page }) => {
    await page.goto('/');
    const toggle = page.locator('.menu-toggle');
    const nav = page.locator('nav.main-nav');

    await toggle.click();
    await expect(nav).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(nav).toBeHidden();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
  });
});
