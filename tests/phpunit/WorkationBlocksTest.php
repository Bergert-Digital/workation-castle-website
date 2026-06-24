<?php

class WorkationBlocksTest extends WP_UnitTestCase {

	private function render( string $markup ): string {
		return do_blocks( $markup );
	}

	public function test_reviews_renders_chrome_and_inner_review() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-reviews -->'
			. '<!-- wp:pediment-child/workation-review {"text":"Loved it","title":"Jane Doe","role":"Guest"} /-->'
			. '<!-- /wp:pediment-child/workation-reviews -->'
		);
		$this->assertStringContainsString( 'reviews-grid', $html );
		$this->assertStringContainsString( 'class="review', $html );
		$this->assertStringContainsString( 'Loved it', $html );
		$this->assertStringContainsString( 'Jane Doe', $html );
		$this->assertStringContainsString( 'Guest', $html );
		$this->assertStringContainsString( '★★★★★', $html );
	}

	public function test_reviews_uses_default_eyebrow_when_absent() {
		$html = $this->render( '<!-- wp:pediment-child/workation-reviews /-->' );
		$this->assertStringContainsString( 'Guest reviews', $html );
	}

	public function test_reviews_cleared_eyebrow_stays_empty() {
		$html = $this->render( '<!-- wp:pediment-child/workation-reviews {"eyebrow":""} /-->' );
		$this->assertStringNotContainsString( 'Guest reviews', $html );
	}

	public function test_empty_review_renders_nothing() {
		$html = $this->render( '<!-- wp:pediment-child/workation-review /-->' );
		$this->assertStringNotContainsString( 'class="review', $html );
	}
}
