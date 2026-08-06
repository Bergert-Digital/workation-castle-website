<?php

class PageHeroBodyClassTest extends WP_UnitTestCase {

	public function test_page_hero_keeps_transparent_overlay_header() {
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:workation/page-hero {"headline":"Hello"} /-->',
			)
		);
		$this->go_to( get_permalink( $id ) );
		$classes = get_body_class();
		// The page-hero is a cinematic image hero, so the header overlays it
		// transparent/white — exactly like the homepage hero.
		$this->assertNotContains( 'no-hero', $classes );
		$this->assertNotContains( 'page-hero-light', $classes );
	}
}
