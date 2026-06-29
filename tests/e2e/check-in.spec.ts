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

test( 'truncates stale guest data when count is reduced after going Back', async ( { page } ) => {
	let capturedBody: { counts: { guests: number; houses: number }; guests: unknown[] } | null = null;

	await page.route( '**/pediment-child/v1/check-in', async ( route ) => {
		capturedBody = route.request().postDataJSON();
		await route.fulfill( {
			status: 200,
			contentType: 'application/json',
			body: JSON.stringify( { ok: true } ),
		} );
	} );

	await page.goto( '/check-in/' );

	// Step 1: set 2 guests.
	await page.fill( 'input[name="guest_count"]', '2' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );

	// Step 2: fill guest 1 (so slot 0 gets data).
	await fillGuest( page, 'Extra', 'Person' );
	await page.click( '.wc-checkin-back' );

	// Back on step 1: reduce to 1 guest.
	await page.fill( 'input[name="guest_count"]', '1' );
	await page.click( '.wc-checkin-next' );

	// Step 2 (guest 1 of 1): fill the single guest.
	await fillGuest( page, 'Jane', 'Doe' );
	await page.click( '.wc-checkin-next' );

	// Step 3: ID for house 1.
	await page.selectOption( 'select[name="doc_type"]', 'passport' );
	await page.fill( 'input[name="doc_number"]', 'X1234567' );
	await page.click( '.wc-checkin-next' );

	// Review: consent and submit.
	await page.check( '.wc-checkin-consent input[type="checkbox"]' );
	await page.click( '.wc-checkin-submit' );

	await expect( page.locator( '.wc-checkin-done' ) ).toBeVisible();

	// Assert payload was truncated: counts.guests===1, guests.length===1.
	expect( capturedBody ).not.toBeNull();
	expect( capturedBody!.counts.guests ).toBe( 1 );
	expect( capturedBody!.guests.length ).toBe( 1 );
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
