// tests/e2e/check-in.spec.ts
import { test, expect } from '@playwright/test';
import { seedConsent } from './utils';

test.beforeEach( async ( { page } ) => {
	await seedConsent( page );
} );

async function fillGuest( page, first: string, last: string ) {
	await page.fill( 'input[name="first_name"]', first );
	await page.fill( 'input[name="last_name"]', last );
	await page.fill( 'input[name="nationality"]', 'British' );
	await page.fill( 'input[name="residence_city"]', 'London' );
	await page.fill( 'input[name="birthdate"]', '1990-05-01' );
	await page.fill( 'input[name="birth_city"]', 'Leeds' );
	await page.check( 'input[name="gender"][value="female"]' );
}

test( 'completes the multi-step check-in', async ( { page } ) => {
	// Mock the REST submit so the test never sends a real email.
	await page.route( '**/pediment-child/v1/check-in', ( route ) =>
		route.fulfill( {
			status: 200,
			contentType: 'application/json',
			body: JSON.stringify( { ok: true } ),
		} )
	);

	await page.goto( '/check-in/' );

	// Step 1: counts.
	await page.fill( 'input[name="guest_count"]', '1' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );

	// Step 2: guest 1.
	await fillGuest( page, 'Jane', 'Doe' );
	await page.click( '.wc-checkin-next' );

	// Step 3: house 1 ID.
	await page.selectOption( 'select[name="doc_type"]', 'passport' );
	await page.fill( 'input[name="doc_number"]', 'X1234567' );
	await page.click( '.wc-checkin-next' );

	// Review: consent gates submit.
	const submit = page.locator( '.wc-checkin-submit' );
	await expect( submit ).toBeDisabled();
	await page.check( '.wc-checkin-consent input[type="checkbox"]' );
	await expect( submit ).toBeEnabled();
	await submit.click();

	await expect( page.locator( '.wc-checkin-done' ) ).toBeVisible();
} );

test( 'step validation blocks advancing with empty required fields', async ( { page } ) => {
	await page.goto( '/check-in/' );
	await page.fill( 'input[name="guest_count"]', '1' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );
	// Try to advance the guest step with everything empty.
	await page.click( '.wc-checkin-next' );
	// Still on the guest step (no ID/doc fields yet).
	await expect( page.locator( 'input[name="first_name"]' ) ).toBeVisible();
	await expect( page.locator( '.wc-checkin-error' ).first() ).toBeVisible();
} );
