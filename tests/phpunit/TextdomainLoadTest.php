<?php

class TextdomainLoadTest extends WP_UnitTestCase {

	public function tear_down() {
		restore_previous_locale();
		parent::tear_down();
	}

	public function test_the_theme_textdomain_is_registered_on_after_setup_theme() {
		$this->assertNotFalse(
			has_action( 'after_setup_theme', 'pediment_child_load_textdomain' ),
			'Without this hook every gettext call falls through to English.'
		);
	}

	public function test_a_german_catalog_ships_and_compiles() {
		// load_child_theme_textdomain() resolves in-theme catalogs by locale
		// alone (no domain prefix) once the language directory lives inside
		// get_stylesheet_directory() — see _load_textdomain_just_in_time().
		$mo = get_stylesheet_directory() . '/languages/de_DE.mo';
		$this->assertFileExists( $mo, 'Run `npm run i18n:mo` and commit the result.' );
	}

	public function test_availability_form_labels_are_german_under_de_de() {
		switch_to_locale( 'de_DE' );
		$html = pediment_child_render_availability_form( array() );

		$this->assertStringContainsString( 'Anreise', $html );
		$this->assertStringContainsString( 'Abreise', $html );
		$this->assertStringContainsString( 'Verfügbarkeit prüfen', $html );
		// The label renders as "</span> Arrival</label>" — the space before the
		// text is load bearing. Without it this needle appears in neither
		// locale and the assertion can never fail.
		$this->assertStringNotContainsString( '> Arrival<', $html );
	}

	public function test_english_is_untouched() {
		$html = pediment_child_render_availability_form( array() );

		$this->assertStringContainsString( 'Arrival', $html );
		$this->assertStringContainsString( 'Check availability', $html );
	}
}
