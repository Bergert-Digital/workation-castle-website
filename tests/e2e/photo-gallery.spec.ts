import { test, expect } from '@playwright/test';

test( 'photo gallery filters and opens lightbox', async ( { page } ) => {
	await page.goto( '/photos/' );

	const photos = page.locator( '.photo-grid .photo' );
	await expect( photos.first() ).toBeVisible();
	const total = await photos.count();
	expect( total ).toBeGreaterThan( 0 );

	// Click the second tab (first category) and expect a subset to remain.
	const tabs = page.locator( '.photo-tab' );
	if ( ( await tabs.count() ) > 1 ) {
		await tabs.nth( 1 ).click();
		await expect( tabs.nth( 1 ) ).toHaveClass( /is-active/ );
		const visible = page.locator( '.photo-grid .photo:not(.is-hidden)' );
		expect( await visible.count() ).toBeLessThanOrEqual( total );
		expect( await visible.count() ).toBeGreaterThan( 0 );
	}

	// Open the lightbox on the first visible photo.
	await page.locator( '.photo-grid .photo:not(.is-hidden)' ).first().click();
	await expect( page.locator( '.wc-lightbox.is-open' ) ).toBeVisible();
	await page.keyboard.press( 'Escape' );
	await expect( page.locator( '.wc-lightbox' ) ).not.toHaveClass( /is-open/ );
} );
