<?php

/**
 * The estate map page's content lives in patterns/map.php, which the seed
 * manifest names as the `map` entry's pattern.
 */
class MapPatternTest extends PatternTestCase {

	public function test_the_pattern_declares_the_map_slug() {
		$source = file_get_contents( $this->theme_dir() . '/patterns/map.php' );

		$this->assertStringContainsString( 'Slug: workation/map', $source );
	}

	public function test_the_pattern_carries_the_estate_map_block() {
		$markup = $this->render_pattern( 'map.php' );

		$this->assertStringContainsString( 'wp:workation/estate-map', $markup );
	}

	public function test_guide_card_links_internally() {
		$guide = file_get_contents( $this->theme_dir() . '/patterns/guide.php' );
		$this->assertStringContainsString( '"linkUrl":"/guide/map/"', $guide );
		$this->assertStringNotContainsString( 'workationcastle.com/guide/map/', $guide );
	}
}
