<?php

/**
 * The guest guide's content lives in patterns/guide.php, which the seed
 * manifest names as the `guide` entry's pattern.
 */
class GuidePatternTest extends PatternTestCase {

	public function test_the_pattern_declares_the_guide_slug() {
		$source = file_get_contents( $this->theme_dir() . '/patterns/guide.php' );

		$this->assertStringContainsString( 'Slug: workation/guide', $source );
	}

	public function test_the_pattern_lays_the_topics_out_in_a_card_grid() {
		$markup = $this->render_pattern( 'guide.php' );
		$grids  = array_values(
			array_filter(
				parse_blocks( $markup ),
				static fn( $block ) => 'core/group' === $block['blockName']
					&& str_contains( $block['attrs']['className'] ?? '', 'guide-cards' )
			)
		);

		$this->assertCount( 1, $grids );
		$this->assertSame( 'grid', $grids[0]['attrs']['layout']['type'] );

		$cards = array_filter(
			$grids[0]['innerBlocks'],
			static fn( $block ) => 'core/group' === $block['blockName']
				&& str_contains( $block['attrs']['className'] ?? '', 'is-style-card' )
		);
		$this->assertCount( 7, $cards );
		$this->assertSame( 7, substr_count( $markup, 'wp:pediment/icon ' ) );
		$this->assertStringNotContainsString( 'wp:pediment/feature-grid', $markup );
	}
}
