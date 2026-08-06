<?php

class ConsentDefuseTest extends WP_UnitTestCase {

	public function test_komoot_iframe_is_defused() {
		$html = '<iframe src="https://www.komoot.com/tour/123/embed?profile=1" width="640" height="600" title="Tour"></iframe>';
		$out  = workation_consent_defuse_iframes( $html );

		// Leading space distinguishes a real ` src=` from `data-consent-src=`.
		$this->assertStringNotContainsString( ' src="https://www.komoot.com', $out, 'real src must be removed' );
		$this->assertStringContainsString( 'data-consent-src="https://www.komoot.com/tour/123/embed?profile=1"', $out );
		$this->assertStringContainsString( 'data-consent-category="functional"', $out );
		$this->assertStringContainsString( 'wc-consent-embed', $out );
		$this->assertStringContainsString( 'Komoot', $out, 'placeholder names the provider' );
	}

	public function test_google_maps_iframe_is_defused() {
		// Single-param src so esc_url leaves it byte-for-byte (no & -> &#038;).
		$html = '<iframe src="https://maps.google.com/maps?output=embed" loading="lazy"></iframe>';
		$out  = workation_consent_defuse_iframes( $html );

		$this->assertStringContainsString( 'data-consent-src="https://maps.google.com/maps?output=embed"', $out );
		$this->assertStringNotContainsString( ' src="https://maps.google.com', $out );
		$this->assertStringContainsString( 'Google Maps', $out );
	}

	public function test_first_party_iframe_is_left_untouched() {
		$html = '<iframe src="https://workationcastle.com/internal/widget" title="Internal"></iframe>';
		$out  = workation_consent_defuse_iframes( $html );

		$this->assertSame( $html, $out, 'non-allowlisted iframe is unchanged' );
	}

	public function test_content_with_no_iframe_is_returned_verbatim() {
		$html = '<p>Just a paragraph with a <a href="https://www.komoot.com/">Komoot link</a>.</p>';
		$this->assertSame( $html, workation_consent_defuse_iframes( $html ) );
	}

	public function test_defuse_is_idempotent() {
		$html  = '<iframe src="https://www.komoot.com/tour/123/embed?profile=1" title="Tour"></iframe>';
		$once  = workation_consent_defuse_iframes( $html );
		$twice = workation_consent_defuse_iframes( $once );

		$this->assertSame( $once, $twice, 'defusing already-defused content is a no-op' );
	}
}
