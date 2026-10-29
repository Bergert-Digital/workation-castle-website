<?php

class ActivityMapEnqueueTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// A fresh queue per test: an earlier enqueue would leak into the next.
		$GLOBALS['wp_scripts'] = null;
		$GLOBALS['wp_styles']  = null;
	}

	private function enqueue_on( int $post_id ) {
		$this->go_to( get_permalink( $post_id ) );
		do_action( 'wp_enqueue_scripts' );
	}

	public function test_a_page_with_the_map_block_loads_leaflet() {
		$page = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:workation/activity-map {"lat":"46.0389","lng":"9.1419","title":"Waste collection point"} /-->',
			)
		);
		$this->enqueue_on( $page );

		$this->assertTrue( wp_script_is( 'leaflet', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'workation-castle-activity-map', 'enqueued' ) );
	}

	public function test_a_page_without_the_map_block_skips_leaflet() {
		$page = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:paragraph --><p>No map here.</p><!-- /wp:paragraph -->',
			)
		);
		$this->enqueue_on( $page );

		$this->assertFalse( wp_script_is( 'leaflet', 'enqueued' ) );
	}
}
