<?php

/**
 * The header CTA lives in a language-less seeded template part, so its label
 * must be translated at render time — the static markup can't do it.
 */
class HeaderCtaTranslationTest extends WP_UnitTestCase {

	private const CTA_BLOCK = '<!-- wp:html --><a class="wc-btn wc-btn-yellow header-cta" href="https://workationcastle.holiduhost.com/">Check availability <span class="arr">→</span></a><!-- /wp:html -->';

	public function test_cta_label_is_translated_when_rendered_in_german() {
		switch_to_locale( 'de_DE' );
		$html = do_blocks( self::CTA_BLOCK );
		restore_previous_locale();

		$this->assertStringContainsString( 'Verfügbarkeit prüfen', $html );
		$this->assertStringNotContainsString( '>Check availability <', $html );
	}

	public function test_cta_label_stays_english_in_the_default_locale() {
		$html = do_blocks( self::CTA_BLOCK );

		$this->assertStringContainsString( '>Check availability <span class="arr">', $html );
	}

	public function test_other_html_blocks_are_left_alone() {
		$block = '<!-- wp:html --><p>Check availability soon</p><!-- /wp:html -->';

		switch_to_locale( 'de_DE' );
		$html = do_blocks( $block );
		restore_previous_locale();

		$this->assertStringContainsString( 'Check availability soon', $html );
	}
}
