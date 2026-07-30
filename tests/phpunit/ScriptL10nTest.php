<?php

class ScriptL10nTest extends WP_UnitTestCase {

	public function test_lightbox_labels_are_localized() {
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );

		$data = wp_scripts()->get_data( 'workation-castle-lightbox', 'data' );
		$this->assertIsString( $data, 'The lightbox has no localized payload.' );
		$this->assertStringContainsString( 'wcLightbox', $data );
		$this->assertStringContainsString( 'Image viewer', $data );
		$this->assertStringContainsString( 'Previous image', $data );
	}

	public function test_activity_map_label_is_localized() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => PEDIMENT_CHILD_ACTIVITY_CPT,
				'post_status' => 'publish',
			)
		);
		$this->go_to( get_permalink( $post_id ) );
		do_action( 'wp_enqueue_scripts' );

		$data = wp_scripts()->get_data( 'workation-castle-activity-map', 'data' );
		$this->assertIsString( $data, 'The activity map has no localized payload.' );
		$this->assertStringContainsString( 'See on Google Maps', $data );
	}
}
