<?php

class ConsentProviderLabelTest extends WP_UnitTestCase {

	public function tear_down() {
		restore_previous_locale();
		parent::tear_down();
	}

	public function test_known_providers_keep_their_brand_names() {
		$this->assertSame( 'Komoot', pediment_child_consent_provider_label( 'https://www.komoot.de/tour/1' ) );
		$this->assertSame( 'Google Maps', pediment_child_consent_provider_label( 'https://maps.google.com/x' ) );
	}

	public function test_the_unknown_provider_fallback_is_translated() {
		switch_to_locale( 'de_DE' );

		$this->assertSame(
			'diesem Anbieter',
			pediment_child_consent_provider_label( 'https://example.com/embed' ),
			'An untranslated fallback renders "gehostet von this provider" mid-sentence.'
		);
	}
}
