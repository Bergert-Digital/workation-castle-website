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

	// KNOWN BUG, not a catalog defect (see task-9-report.md): patterns/home.php
	// hardcodes primaryText="Check availability" straight into post_content
	// (commit a9724aa — deliberately, so Polylang/DeepL page-content translation
	// can pick it up). WorkationSections.php then feeds that attribute into
	// AvailabilityForm's submit_text, which *bypasses* the __() default
	// entirely — the gettext catalog is never consulted. Locally the German
	// "Startseite" page (post 227) exists as a real Polylang translation, but
	// its content was never actually translated, so the button renders the
	// English default regardless of catalog health. Kept as `test.fail()`
	// rather than skipped or deleted: it documents the exact expected string
	// and will flip green (flagging loudly) once the page content is
	// translated, at which point this annotation should be removed.
	test( 'the hero submit button is translated (blocked by untranslated page content, not the catalog)', async ( { page } ) => {
		test.fail();
		const response = await page.goto( '/de/' );
		test.skip( !response || response.status() === 404, '/de/ 404s — Polylang languages not configured locally' );

		const form = page.locator( 'form.avail' ).first();
		await expect( form.getByRole( 'button', { name: /Verfügbarkeit prüfen/ } ) ).toBeVisible();
		await expect( form ).not.toContainText( 'Check availability' );
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

	// KNOWN BUG, sitewide, not a catalog defect (see task-9-report.md):
	// parts/header.html and parts/footer.html are plain file-based template
	// parts (functions.php ~L429-460 explicitly forces the file-based header
	// to win over any DB-backed copy). Both files hardcode the
	// "Check availability" CTA as literal `wp:html`/anchor markup, which is
	// never run through __() and is byte-identical for every Polylang
	// language — in fact the whole footer (nav labels, legal links, copyright)
	// is literal English text, never wrapped at all. The gettext catalog
	// already has the right translation ("Verfügbarkeit prüfen") — it is
	// simply never consulted here. This is separate from, and larger than,
	// the hero-submit-button gap above: it affects the header CTA and footer
	// link on every page, in every language. Kept as `test.fail()` for the
	// same reason as above.
	test( 'the header and footer "Check availability" CTAs are translated', async ( { page } ) => {
		test.fail();
		const response = await page.goto( '/de/' );
		test.skip( !response || response.status() === 404, '/de/ 404s — Polylang languages not configured locally' );

		await expect( page.locator( '.header-cta' ) ).toHaveText( /Verfügbarkeit prüfen/ );
		await expect( page.locator( 'footer' ).getByRole( 'link', { name: /Verfügbarkeit prüfen/ } ) ).toBeVisible();
	} );
} );
