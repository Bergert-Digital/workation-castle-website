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

	public function test_the_pattern_lays_the_topics_out_in_a_feature_grid() {
		$markup = $this->render_pattern( 'guide.php' );

		$this->assertStringContainsString( 'wp:pediment/feature-grid', $markup );
	}
}
