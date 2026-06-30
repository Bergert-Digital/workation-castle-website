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
	await page.selectOption( 'select[name="guest_index"]', '0' );
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

test( 'associates an ID with a chosen guest and shows it in review', async ( { page } ) => {
	await page.route( '**/pediment-child/v1/check-in', ( route ) =>
		route.fulfill( {
			status: 200,
			contentType: 'application/json',
			body: JSON.stringify( { ok: true } ),
		} )
	);

	await page.goto( '/check-in/' );
	await page.fill( 'input[name="guest_count"]', '2' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );
	await fillGuest( page, 'Alice', 'Anderson' );
	await page.click( '.wc-checkin-next' );
	await fillGuest( page, 'Bob', 'Brown' );
	await page.click( '.wc-checkin-next' );

	// The ID step lists both entered guests by name; pick the second (Bob).
	const guestSelect = page.locator( 'select[name="guest_index"]' );
	await expect( guestSelect.locator( 'option' ) ).toHaveText( [
		'—',
		'Alice Anderson',
		'Bob Brown',
	] );
	await guestSelect.selectOption( '1' );
	await page.selectOption( 'select[name="doc_type"]', 'passport' );
	await page.fill( 'input[name="doc_number"]', 'P999' );
	await page.click( '.wc-checkin-next' );

	// Review shows the document belongs to Bob Brown.
	const reviewIds = page.locator( '.wc-checkin-review-ids' );
	await expect( reviewIds ).toContainText( 'Bob Brown' );
	await expect( reviewIds ).toContainText( 'P999' );
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
	await page.selectOption( 'select[name="guest_index"]', '0' );
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

test( 'blocks a future birthdate client-side', async ( { page } ) => {
	await page.goto( '/check-in/' );
	await page.fill( 'input[name="guest_count"]', '1' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );

	// Everything valid except a birthdate in the future.
	await page.fill( 'input[name="first_name"]', 'Jane' );
	await page.fill( 'input[name="last_name"]', 'Doe' );
	await page.fill( 'input[name="nationality"]', 'British' );
	await page.fill( 'input[name="residence_city"]', 'London' );
	await page.fill( 'input[name="birthdate"]', '2999-01-01' );
	await page.fill( 'input[name="birth_city"]', 'Leeds' );
	await page.check( 'input[name="gender"][value="female"]' );
	await page.click( '.wc-checkin-next' );

	// Stays on the guest step with a birthdate error — never reaches the ID step.
	await expect( page.locator( 'input[name="first_name"]' ) ).toBeVisible();
	await expect(
		page.locator( '[data-field="birthdate"] .wc-checkin-error' )
	).toBeVisible();
} );

test( 'surfaces a server validation error on the offending step', async ( { page } ) => {
	// Server rejects with a field error; the wizard should jump back to that
	// guest and show the message instead of an opaque generic failure.
	await page.route( '**/pediment-child/v1/check-in', ( route ) =>
		route.fulfill( {
			status: 400,
			contentType: 'application/json',
			body: JSON.stringify( {
				ok: false,
				errors: { 'guests.0.birthdate': 'Enter a valid birthdate.' },
			} ),
		} )
	);

	await page.goto( '/check-in/' );
	await page.fill( 'input[name="guest_count"]', '1' );
	await page.fill( 'input[name="house_count"]', '1' );
	await page.click( '.wc-checkin-next' );
	await fillGuest( page, 'Jane', 'Doe' );
	await page.click( '.wc-checkin-next' );
	await page.selectOption( 'select[name="guest_index"]', '0' );
	await page.selectOption( 'select[name="doc_type"]', 'passport' );
	await page.fill( 'input[name="doc_number"]', 'X1234567' );
	await page.click( '.wc-checkin-next' );
	await page.check( '.wc-checkin-consent input[type="checkbox"]' );
	await page.click( '.wc-checkin-submit' );

	// Jumped back to guest 1 with the server's exact message on the field.
	await expect( page.locator( 'input[name="first_name"]' ) ).toBeVisible();
	const err = page.locator( '[data-field="birthdate"] .wc-checkin-error' );
	await expect( err ).toBeVisible();
	await expect( err ).toHaveText( 'Enter a valid birthdate.' );
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
