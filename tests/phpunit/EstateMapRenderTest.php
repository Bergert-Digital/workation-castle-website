<?php

class EstateMapRenderTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Allow get_block_wrapper_attributes() to run outside a block render context.
		WP_Block_Supports::$block_to_render = array( 'blockName' => '', 'attrs' => array() );
	}

	public function tearDown(): void {
		WP_Block_Supports::$block_to_render = null;
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
}
