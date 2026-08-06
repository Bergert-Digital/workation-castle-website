<?php

class PageHeroRenderTest extends WP_UnitTestCase {

	public function test_renders_eyebrow_headline_and_lead() {
		$html = workation_page_hero_chrome(
			array(
				'eyebrow'  => 'Gallery',
				'headline' => 'Every corner of the castle',
				'lead'     => 'Wander the whole estate before you arrive.',
			)
		);
		$this->assertStringContainsString( 'page-hero', $html );
		$this->assertStringContainsString( 'Gallery', $html );
		$this->assertStringContainsString( 'Every corner of the castle', $html );
		$this->assertStringContainsString( 'Wander the whole estate before you arrive.', $html );
	}

	public function test_renders_image_and_gradient() {
		$html = workation_page_hero_chrome(
			array(
				'headline' => 'Hi',
				'imageUrl' => 'https://example.com/castle.jpg',
				'imageAlt' => 'The castle',
			)
		);
		$this->assertStringContainsString( 'page-hero-img', $html );
		$this->assertStringContainsString( 'page-hero-grad', $html );
		$this->assertStringContainsString( 'src="https://example.com/castle.jpg"', $html );
		$this->assertStringContainsString( 'alt="The castle"', $html );
	}

	public function test_falls_back_to_bundled_hero_image_when_none_set() {
		$html = workation_page_hero_chrome( array( 'headline' => 'Hi' ) );
		$this->assertStringContainsString( 'assets/images/hero-default.jpg', $html );
		$this->assertStringContainsString( 'page-hero-img', $html );
	}

	public function test_does_not_render_stats_or_watermark() {
		$html = workation_page_hero_chrome(
			array(
				'headline' => 'Hi',
				'stats'    => array( array( 'value' => '5', 'label' => 'spaces' ) ),
			)
		);
		$this->assertStringNotContainsString( 'page-hero-stats', $html );
		$this->assertStringNotContainsString( 'page-hero-mark', $html );
	}

	public function test_escapes_image_url_and_alt() {
		$html = workation_page_hero_chrome(
			array(
				'headline' => 'Hi',
				'imageUrl' => 'https://example.com/x.jpg" onerror="alert(1)',
				'imageAlt' => 'A "quote" & ampersand',
			)
		);
		$this->assertStringNotContainsString( 'onerror="alert(1)"', $html );
		$this->assertStringNotContainsString( 'A "quote"', $html );
	}
}
