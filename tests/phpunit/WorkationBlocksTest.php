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

	public function test_spaces_first_row_not_reversed_second_reversed() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-spaces -->'
			. '<!-- wp:pediment-child/workation-space {"title":"Workspace","text":"Focus.","imageUrl":"https://example.com/1.jpg"} /-->'
			. '<!-- wp:pediment-child/workation-space {"title":"Homes","text":"Stay.","imageUrl":"https://example.com/2.jpg"} /-->'
			. '<!-- /wp:pediment-child/workation-spaces -->'
		);
		$this->assertStringContainsString( 'space-row', $html );
		$this->assertStringContainsString( 'space-row reverse', $html );
		// Exactly one reversed row for two items.
		$this->assertSame( 1, substr_count( $html, 'space-row reverse' ) );
	}

	/**
	 * A 4-row spaces render produces exactly 2 occurrences of "space-row reverse"
	 * (indices 1 and 3) and ZERO occurrences of "space-row reverse reverse".
	 */
	public function test_spaces_four_rows_produce_two_reversed_no_double_reverse() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-spaces -->'
			. '<!-- wp:pediment-child/workation-space {"title":"A","text":"a","imageUrl":"https://example.com/1.jpg"} /-->'
			. '<!-- wp:pediment-child/workation-space {"title":"B","text":"b","imageUrl":"https://example.com/2.jpg"} /-->'
			. '<!-- wp:pediment-child/workation-space {"title":"C","text":"c","imageUrl":"https://example.com/3.jpg"} /-->'
			. '<!-- wp:pediment-child/workation-space {"title":"D","text":"d","imageUrl":"https://example.com/4.jpg"} /-->'
			. '<!-- /wp:pediment-child/workation-spaces -->'
		);
		// Exactly 2 reversed rows (indices 1 and 3).
		$this->assertSame( 2, substr_count( $html, 'space-row reverse' ) );
		// No double-reverse.
		$this->assertStringNotContainsString( 'space-row reverse reverse', $html );
	}

	public function test_location_renders_map_and_modes() {
		// Use non-default values for the child block so the test can only pass
		// if workation-mode/render.php actually processed the passed attributes
		// (not a legacy-default leak from pediment_child_workation_section_defaults).
		$html = $this->render(
			'<!-- wp:pediment-child/workation-location {"imageUrl":"https://example.com/themap.png","imageAlt":"Map"} -->'
			. '<!-- wp:pediment-child/workation-mode {"title":"Helicopter","text":"Pad on roof","icon":"plane"} /-->'
			. '<!-- /wp:pediment-child/workation-location -->'
		);
		// Structural: location chrome renders correctly.
		$this->assertStringContainsString( 'loc-grid', $html );
		$this->assertStringContainsString( 'loc-map', $html );
		// Parent-level map image URL (distinctive, passed via block attributes).
		$this->assertStringContainsString( 'themap.png', $html );
		// modes region wrapper.
		$this->assertStringContainsString( 'class="modes', $html );
		// Child block rendered: non-default title and text from passed attributes.
		$this->assertStringContainsString( 'Helicopter', $html );
		$this->assertStringContainsString( 'Pad on roof', $html );
		// Bare mode-icon span is present (render.php emits <span class="mode-icon">).
		$this->assertStringContainsString( 'mode-icon', $html );
		// No per-icon class leaked (render.php must NOT emit mode-icon--* classes).
		$this->assertStringNotContainsString( 'mode-icon--', $html );
		// Negative: default-path strings must NOT appear (proves no legacy-default leak).
		$this->assertStringNotContainsString( 'By car', $html );
		$this->assertStringNotContainsString( 'Free parking', $html );
	}

	public function test_hero_renders_form_and_chip() {
		$html = $this->render(
			'<!-- wp:pediment-child/workation-hero -->'
			. '<!-- wp:pediment-child/workation-chip {"title":"Up to 9 guests"} /-->'
			. '<!-- /wp:pediment-child/workation-hero -->'
		);
		$this->assertStringContainsString( 'class="hero', $html );
		$this->assertStringContainsString( '<form class="avail"', $html );
		$this->assertStringContainsString( 'hero-chips', $html );
		$this->assertStringContainsString( 'class="chip', $html );
		$this->assertStringContainsString( 'Up to 9 guests', $html );
	}

	public function test_hero_default_headline() {
		$html = $this->render( '<!-- wp:pediment-child/workation-hero /-->' );
		$this->assertStringContainsString( 'work, gather', $html );
	}

	/**
	 * Idempotency: calling pediment_child_workation_mark_reverse_rows() twice on
	 * its own output yields the SAME string as calling it once (no growth of " reverse").
	 */
	public function test_mark_reverse_rows_is_idempotent() {
		// Build a synthetic 4-row spaces HTML that mimics what the block renders.
		$input = '<div class="space-row"><p>Row 0</p></div>'
			. '<div class="space-row"><p>Row 1</p></div>'
			. '<div class="space-row"><p>Row 2</p></div>'
			. '<div class="space-row"><p>Row 3</p></div>';

		$once  = pediment_child_workation_mark_reverse_rows( $input );
		$twice = pediment_child_workation_mark_reverse_rows( $once );

		// Second pass must not grow the string.
		$this->assertSame( $once, $twice, 'pediment_child_workation_mark_reverse_rows() must be idempotent' );
		// Confirm the correct rows ARE reversed after one pass.
		$this->assertSame( 2, substr_count( $once, 'space-row reverse' ) );
		$this->assertStringNotContainsString( 'space-row reverse reverse', $twice );
	}
}
