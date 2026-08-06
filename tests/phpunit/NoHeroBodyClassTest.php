<?php

class NoHeroBodyClassTest extends WP_UnitTestCase {

	public function test_page_with_hero_block_is_not_flagged_no_hero() {
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:workation/workation-hero /-->',
			)
		);
		$this->go_to( get_permalink( $id ) );
		$this->assertNotContains( 'no-hero', get_body_class() );
	}

	public function test_page_without_hero_block_is_flagged_no_hero() {
		$id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<p>Just some text, no cinematic hero.</p>',
			)
		);
		$this->go_to( get_permalink( $id ) );
		$this->assertContains( 'no-hero', get_body_class() );
	}
}
