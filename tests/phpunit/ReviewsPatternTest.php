<?php

/**
 * The reviews page's content lives in patterns/reviews.php, which the seed
 * manifest names as the `reviews` entry's pattern.
 */
class ReviewsPatternTest extends PatternTestCase {

	public function test_the_pattern_declares_the_reviews_slug() {
		$source = file_get_contents( $this->theme_dir() . '/patterns/reviews.php' );

		$this->assertStringContainsString( 'Slug: pediment-child/reviews', $source );
	}

	public function test_the_pattern_carries_the_reviews_block() {
		$markup = $this->render_pattern( 'reviews.php' );

		$this->assertStringContainsString( 'wp:pediment-child/workation-reviews', $markup );
	}

	public function test_the_pattern_lists_all_fifteen_reviewers() {
		$markup = $this->render_pattern( 'reviews.php' );

		$names = array(
			'Eloisa R.', 'Kathrin K.', 'Nicole R.', 'Daniel V.', 'Simone S.',
			'Maria L.', 'Philippe R.', 'Alexandros P.', 'Anna-Lena F.', 'Alexander M.',
			'Manuelle B.', 'Liesbeth L.', 'Irma S.', 'Corinne O.', 'Ed K.',
		);
		foreach ( $names as $name ) {
			$this->assertStringContainsString( $name, $markup );
		}
		$this->assertSame( 15, substr_count( $markup, 'wp:pediment-child/workation-review ' ) );
	}
}
