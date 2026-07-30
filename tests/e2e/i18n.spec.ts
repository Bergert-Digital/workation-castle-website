import { test, expect } from '@playwright/test';

test.describe( 'German front end', () => {
	test( 'the hero availability form chrome is translated', async ( { page } ) => {
		const response = await page.goto( '/de/' );
		test.skip( !response || response.status() === 404, '/de/ 404s — Polylang languages not configured locally' );

		const form = page.locator( 'form.avail' ).first();
		await expect( form ).toBeVisible();
		await expect( form ).toContainText( /Anreise|Zeitraum wählen/ );
		await expect( form ).not.toContainText( 'Arrival' );
	} );

	test( 'the consent modal is translated', async ( { page } ) => {
		await page.context().clearCookies();
		const response = await page.goto( '/de/' );
		test.skip( !response || response.status() === 404, '/de/ 404s — Polylang languages not configured locally' );

		const modal = page.locator( '.wc-consent-modal__panel' );
		await expect( modal ).toBeVisible();
		await expect( modal.locator( '.wc-consent-modal__title' ) ).toHaveText( 'Deine Privatsphäre' );
		await expect( modal.getByRole( 'button', { name: 'Alle akzeptieren' } ) ).toBeVisible();
	} );
} );
