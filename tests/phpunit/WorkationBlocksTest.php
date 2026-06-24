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

	public function test_activities_renders_tile_with_image_and_title() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-activities -->'
			. '<!-- wp:pediment-child/workation-tile {"title":"Swim","imageUrl":"https://example.com/a.jpg","imageAlt":"Lake"} /-->'
			. '<!-- /wp:pediment-child/workation-activities -->'
		);
		$this->assertStringContainsString( 'act-grid', $html );
		$this->assertStringContainsString( 'class="act', $html );
		$this->assertStringContainsString( 'https://example.com/a.jpg', $html );
		$this->assertStringContainsString( 'alt="Lake"', $html );
		$this->assertStringContainsString( 'Swim', $html );
	}

	public function test_activities_uses_default_lead_when_absent() {
		$html = $this->render( '<!-- wp:pediment-child/workation-activities /-->' );
		$this->assertStringContainsString( 'When laptops close', $html );
	}

	public function test_gallery_photo_variant_class() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-gallery -->'
			. '<!-- wp:pediment-child/workation-photo {"imageUrl":"https://example.com/g.jpg","imageAlt":"Room","variant":"wide"} /-->'
			. '<!-- /wp:pediment-child/workation-gallery -->'
		);
		$this->assertStringContainsString( 'class="gallery', $html );
		$this->assertStringContainsString( 'g-wide', $html );
		$this->assertStringContainsString( 'https://example.com/g.jpg', $html );
	}

	public function test_gallery_photo_no_variant_has_no_g_class() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-gallery -->'
			. '<!-- wp:pediment-child/workation-photo {"imageUrl":"https://example.com/g.jpg","imageAlt":"Room"} /-->'
			. '<!-- /wp:pediment-child/workation-gallery -->'
		);
		$this->assertStringNotContainsString( 'g-tall', $html );
		$this->assertStringNotContainsString( 'g-wide', $html );
	}

	public function test_audience_card_renders_full_card() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-audience -->'
			. '<!-- wp:pediment-child/workation-card {"eyebrow":"01 — Team","title":"Team retreats","text":"Beds for all.","linkText":"Plan","linkUrl":"#book","imageUrl":"https://example.com/c.jpg","imageAlt":"Room"} /-->'
			. '<!-- /wp:pediment-child/workation-audience -->'
		);
		$this->assertStringContainsString( 'ways-grid', $html );
		$this->assertStringContainsString( 'class="way', $html );
		$this->assertStringContainsString( 'way-body', $html );
		$this->assertStringContainsString( 'way-num', $html );
		$this->assertStringContainsString( 'Team retreats', $html );
		$this->assertStringContainsString( 'href="#book"', $html );
		$this->assertStringContainsString( 'https://example.com/c.jpg', $html );
	}
}
