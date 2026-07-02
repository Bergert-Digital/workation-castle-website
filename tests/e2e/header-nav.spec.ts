import { test, expect } from '@playwright/test';
import { seedConsent } from './utils';

test.beforeEach(async ({ page }) => {
	await seedConsent(page);
});

test('desktop header renders the seeded navigation links', async ({ page }) => {
	await page.setViewportSize({ width: 1200, height: 800 });
	await page.goto('/');
	const nav = page.locator('.site-header nav.wp-block-navigation');
	await expect(nav).toBeVisible();
	await expect(nav.getByRole('link', { name: 'Activities' })).toBeVisible();
	await expect(nav.getByRole('link', { name: 'Photos' })).toBeVisible();
	await expect(nav.getByText('Ways to stay')).toBeVisible();
	await expect(nav.getByText('Guest Guide')).toBeVisible();
});

test('a submenu exposes its child links', async ({ page }) => {
	await page.setViewportSize({ width: 1200, height: 800 });
	await page.goto('/');
	const waysToStay = page
		.locator('.site-header .wp-block-navigation-submenu')
		.filter({ hasText: 'Ways to stay' });
	await waysToStay.hover();
	await expect(page.getByRole('link', { name: 'Team retreats' })).toBeVisible();
});

test('mobile shows the native hamburger overlay with the items', async ({ page }) => {
	await page.setViewportSize({ width: 480, height: 800 });
	await page.goto('/');
	const open = page.locator('.site-header .wp-block-navigation__responsive-container-open');
	await expect(open).toBeVisible();
	await open.click();
	const overlay = page.locator('.site-header .wp-block-navigation__responsive-container.is-menu-open');
	await expect(overlay).toBeVisible();
	await expect(overlay.getByRole('link', { name: 'Activities' })).toBeVisible();
});
