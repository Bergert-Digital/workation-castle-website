<?php

class EstateMapRenderTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Allow get_block_wrapper_attributes() to run outside a block render context.
		WP_Block_Supports::$block_to_render = array( 'blockName' => '', 'attrs' => array() );
	}

	public function tearDown(): void {
		WP_Block_Supports::$block_to_render = null;
		restore_previous_locale();
		parent::tearDown();
	}

	public function test_every_poi_has_a_pin_and_a_legend_row() {
		$html = workation_estate_map_chrome();
		foreach ( workation_estate_map_pois() as $poi ) {
			// pin + legend row (+ building group for places) => at least 2.
			$this->assertGreaterThanOrEqual(
				2,
				substr_count( $html, 'data-poi="' . $poi['id'] . '"' ),
				"POI {$poi['id']} should appear on both map and legend"
			);
			// esc_html() entity-encodes names containing "&" (e.g. "Courtyard & garden").
			$this->assertStringContainsString( esc_html( $poi['name'] ), $html );
		}
	}

	public function test_place_pois_have_a_building_group() {
		$html = workation_estate_map_chrome();
		// Waste is a labelled point (pin only), so it is not in this list.
		foreach ( array( 'galbiga', 'coworking', 'bar', 'tremezzo' ) as $id ) {
			$this->assertStringContainsString(
				'class="estate-map__building" data-poi="' . $id . '"',
				$html
			);
		}
	}

	public function test_accessible_and_namespaced() {
		$html = workation_estate_map_chrome();
		$this->assertStringContainsString( 'role="img"', $html );
		$this->assertStringContainsString( 'estate-map__legend', $html );
		$this->assertStringContainsString( 'estate-map__svg', $html );
	}

	public function test_block_renders_via_do_blocks() {
		$html = do_blocks( '<!-- wp:workation/estate-map /-->' );
		$this->assertStringContainsString( 'estate-map__svg', $html );
		$this->assertStringContainsString( 'Casa Galbiga', $html );
	}

	public function test_poi_names_and_subtitles_are_translatable() {
		switch_to_locale( 'de_DE' );
		$pois = pediment_child_estate_map_pois();

		$by_id = array_column( $pois, null, 'id' );
		$this->assertSame( 'Arbeitsraum', $by_id['coworking']['name'] );
		$this->assertSame( 'Gästehaus', $by_id['galbiga']['sub'] );
		$this->assertSame( 'Parkplatz', $by_id['parking']['name'] );
	}

	public function test_proper_nouns_are_not_wrapped_for_translation() {
		// __() on an untranslated msgid returns the msgid, so asserting the
		// rendered value cannot tell a literal from a wrapped-but-untranslated
		// string. Guard the source instead: brand names must never reach a
		// translator or DeepL.
		$source = file_get_contents( get_stylesheet_directory() . '/inc/EstateMap.php' );

		$this->assertMatchesRegularExpression( "/'name'\\s*=>\\s*'Casa Galbiga'/", $source );
		$this->assertMatchesRegularExpression( "/'name'\\s*=>\\s*'Casa Tremezzo'/", $source );
		$this->assertDoesNotMatchRegularExpression( "/__\\(\\s*'Casa /", $source );
	}
}
