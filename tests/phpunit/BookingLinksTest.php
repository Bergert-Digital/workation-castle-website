<?php
/**
 * Every "Check availability" call to action points at the Holidu booking
 * partner, but the markup hardcoded the bare URL, so the booking flow always
 * opened in the default language. These tests lock in the render-time fix: a
 * Holidu href gains `language=<current slug>` while every other URL passes
 * through untouched, and the rewrite is idempotent.
 *
 * @package Workation
 */

class BookingLinksTest extends PatternTestCase {

	/** Force a language slug through the filter for the duration of a test. */
	private function with_language( string $slug ): void {
		add_filter(
			'workation_booking_language',
			static function () use ( $slug ) {
				return $slug;
			}
		);
	}

	public function tear_down() {
		remove_all_filters( 'workation_booking_language' );
		parent::tear_down();
	}

	public function test_appends_language_to_a_holidu_url() {
		$this->with_language( 'fr' );

		$this->assertSame(
			'https://workationcastle.holiduhost.com/?language=fr',
			workation_localize_booking_url( 'https://workationcastle.holiduhost.com/' )
		);
	}

	public function test_leaves_non_holidu_urls_untouched() {
		$this->with_language( 'fr' );

		$untouched = array(
			'https://workationcastle.com/',
			'/contact-us/',
			'mailto:info@workationcastle.com',
			'#book',
			'',
		);

		foreach ( $untouched as $url ) {
			$this->assertSame( $url, workation_localize_booking_url( $url ), $url );
		}
	}

	public function test_no_language_leaves_the_url_untouched() {
		// No filter set: Polylang is inactive, so the slug is empty.
		$this->assertSame(
			'https://workationcastle.holiduhost.com/',
			workation_localize_booking_url( 'https://workationcastle.holiduhost.com/' )
		);
	}

	public function test_is_idempotent() {
		$this->with_language( 'de' );

		$once  = workation_localize_booking_url( 'https://workationcastle.holiduhost.com/' );
		$twice = workation_localize_booking_url( $once );

		$this->assertSame( 'https://workationcastle.holiduhost.com/?language=de', $once );
		$this->assertSame( $once, $twice );
	}

	public function test_render_filter_rewrites_booking_href() {
		$this->with_language( 'it' );

		$html     = '<a class="header-cta" href="https://workationcastle.holiduhost.com/">Check availability</a>';
		$rendered = workation_localize_booking_links( $html );

		$this->assertStringContainsString( 'language=it', $rendered );
		$this->assertStringNotContainsString( 'href="https://workationcastle.holiduhost.com/"', $rendered );
	}

	public function test_render_filter_passes_through_content_without_booking_links() {
		$this->with_language( 'fr' );

		$html = '<a href="/contact-us/">Contact</a><p>No booking link here.</p>';

		$this->assertSame( $html, workation_localize_booking_links( $html ) );
	}

	/** The header CTA must be a translatable token, not hardcoded English. */
	public function test_header_pattern_uses_translatable_token() {
		$header = file_get_contents( $this->theme_dir() . '/patterns/header.php' );

		$this->assertStringContainsString( '%WC_T_CHECK_AVAIL%', $header );
		$this->assertStringNotContainsString( '>Check availability <span', $header );
	}

	/** A GET form drops its action query, so language rides as a hidden field. */
	public function test_availability_form_carries_language_as_hidden_field() {
		$this->with_language( 'es' );

		$html = workation_render_availability_form( array() );

		$this->assertStringContainsString( '<input type="hidden" name="language" value="es">', $html );
	}

	public function test_availability_form_omits_language_field_when_absent() {
		$html = workation_render_availability_form( array() );

		$this->assertStringNotContainsString( 'name="language"', $html );
	}
}
