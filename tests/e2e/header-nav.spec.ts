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
	// Labels come from each entry's own post_title in seed/manifest.php now, so
	// they read "Ways to Stay" and "Guide" rather than the retired custom labels.
	await expect(nav.getByText('Ways to Stay')).toBeVisible();
	await expect(nav.getByText('Guide')).toBeVisible();
});

test('a submenu exposes its child links', async ({ page }) => {
	await page.setViewportSize({ width: 1200, height: 800 });
	await page.goto('/');
	const waysToStay = page
		.locator('.site-header .wp-block-navigation-submenu')
		.filter({ hasText: 'Ways to Stay' });
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

// Regression guard: the open overlay must keep its dark brand background. Core's
// default paints it white via a `:not(.has-background)...:not(.disable-default-overlay)`
// selector; WordPress 7.0 widened that selector, out-specifying the theme's old
// override so the overlay went white while its links stayed #fff — white on white.
test('mobile overlay keeps a dark background that contrasts its links', async ({
	page,
}) => {
	await page.setViewportSize({ width: 480, height: 800 });
	await page.goto('/');
	await page.locator('.site-header .wp-block-navigation__responsive-container-open').click();
	const overlay = page.locator('.site-header .wp-block-navigation__responsive-container.is-menu-open');
	await expect(overlay).toBeVisible();

	const { bg, brown, link } = await overlay.evaluate((el) => {
		const links = el.querySelector('.wp-block-navigation-item__content');
		return {
			bg: getComputedStyle(el).backgroundColor,
			link: links ? getComputedStyle(links).color : '',
			brown: getComputedStyle(document.documentElement)
				.getPropertyValue('--wc-brown')
				.trim(),
		};
	});
	// The overlay resolves --wc-brown (#3A2616) rather than core's default white.
	expect(bg).toBe('rgb(58, 38, 22)');
	expect(brown.toUpperCase()).toBe('#3A2616');
	// And the links are legible against it (not the same colour as the background).
	expect(link).not.toBe(bg);
});

// Regression guard: the parent theme puts backdrop-filter on the header once it
// turns solid (.scrolled). backdrop-filter makes the header the containing block
// for position:fixed descendants, so the core overlay's inset:0 resolved against
// the header bar instead of the viewport — the menu opened as a 64px-tall strip.
test('mobile overlay covers the viewport after scrolling', async ({ page }) => {
	await page.setViewportSize({ width: 390, height: 844 });
	await page.goto('/');
	await page.evaluate(() => window.scrollTo(0, 600));
	await expect(page.locator('.site-header.scrolled')).toBeVisible();
	await page.locator('.site-header .wp-block-navigation__responsive-container-open').click();
	const overlay = page.locator('.site-header .wp-block-navigation__responsive-container.is-menu-open');
	await expect(overlay).toBeVisible();
	// Sub-pixel layout can report e.g. 843.9999; the broken state was 64px tall.
	const box = await overlay.boundingBox();
	expect(box?.height ?? 0).toBeGreaterThanOrEqual(843);
	expect(box?.width ?? 0).toBeGreaterThanOrEqual(389);
});

// Regression guard: core's Navigation block only collapses below 600px, but the
// theme collapses to the hamburger up to 899px (matching the old nav). Without
// the override the links render inline with no hamburger on tablet widths.
test('tablet width (768px) collapses to the hamburger', async ({ page }) => {
	await page.setViewportSize({ width: 768, height: 800 });
	await page.goto('/');
	const open = page.locator('.site-header .wp-block-navigation__responsive-container-open');
	await expect(open).toBeVisible();
	// The inline links are hidden behind the hamburger until it is opened.
	await expect(
		page.locator('.site-header .wp-block-navigation__responsive-container:not(.is-menu-open)')
	).toBeHidden();
	await open.click();
	const overlay = page.locator('.site-header .wp-block-navigation__responsive-container.is-menu-open');
	await expect(overlay).toBeVisible();
	await expect(overlay.getByRole('link', { name: 'Photos' })).toBeVisible();
});
