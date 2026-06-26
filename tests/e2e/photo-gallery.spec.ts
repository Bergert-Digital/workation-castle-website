import { test, expect } from '@playwright/test';

test( 'photo gallery filters and opens lightbox', async ( { page } ) => {
	await page.goto( '/photos/' );

	const photos = page.locator( '.photo-grid .photo' );
	await expect( photos.first() ).toBeVisible();
	const total = await photos.count();
	expect( total ).toBeGreaterThan( 0 );

	// Click a category tab and expect a strict, matching subset to remain.
	const tabs = page.locator( '.photo-tab' );
	expect( await tabs.count() ).toBeGreaterThan( 1 );
	const tab = tabs.nth( 1 );
	const slug = await tab.getAttribute( 'data-filter' );
	await tab.click();
	await expect( tab ).toHaveClass( /is-active/ );

	const visible = page.locator( '.photo-grid .photo:not(.is-hidden)' );
	const visibleCount = await visible.count();
	expect( visibleCount ).toBeGreaterThan( 0 );
	// A real filter narrows the set: fewer than the full gallery...
	expect( visibleCount ).toBeLessThan( total );
	// ...and every remaining photo carries the selected category.
	const mismatched = page.locator(
		`.photo-grid .photo:not(.is-hidden):not([data-category~="${ slug }"])`
	);
	expect( await mismatched.count() ).toBe( 0 );

	// Open the lightbox on the first visible photo.
	await page.locator( '.photo-grid .photo:not(.is-hidden)' ).first().click();
	await expect( page.locator( '.wc-lightbox.is-open' ) ).toBeVisible();
	await page.keyboard.press( 'Escape' );
	await expect( page.locator( '.wc-lightbox' ) ).not.toHaveClass( /is-open/ );
} );
