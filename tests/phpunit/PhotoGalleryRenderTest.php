<?php

class PhotoGalleryRenderTest extends WP_UnitTestCase {

	private function make_photo( string $title, string $term_slug, int $order ): int {
		$term = wp_insert_term( ucfirst( $term_slug ), 'wc_photo_category', array( 'slug' => $term_slug ) );
		$id   = self::factory()->post->create(
			array(
				'post_type'   => 'wc_photo',
				'post_title'  => $title,
				'post_status' => 'publish',
				'menu_order'  => $order,
			)
		);
		wp_set_object_terms( $id, array( is_wp_error( $term ) ? get_term_by( 'slug', $term_slug, 'wc_photo_category' )->term_id : $term['term_id'] ), 'wc_photo_category' );
		// Fake a featured image so the URL helper resolves a 'full' URL.
		$att = self::factory()->attachment->create_object(
			'photo.jpg',
			$id,
			array( 'post_mime_type' => 'image/jpeg', 'post_type' => 'attachment' )
		);
		update_post_meta( $att, '_wp_attached_file', '2024/01/photo.jpg' );
		set_post_thumbnail( $id, $att );
		return $id;
	}

	public function test_renders_grid_with_photo_anchor_and_category() {
		$this->make_photo( 'Lakeside', 'casa-galbiga', 10 );
		$html = pediment_child_photo_gallery_chrome( array() );
		$this->assertStringContainsString( 'photo-grid', $html );
		$this->assertStringContainsString( 'class="photo"', $html );
		$this->assertStringContainsString( 'data-category="casa-galbiga"', $html );
	}

	public function test_renders_tabs_with_all_plus_used_terms() {
		$this->make_photo( 'A', 'casa-galbiga', 10 );
		$this->make_photo( 'B', 'casa-tremezzo', 20 );
		$html = pediment_child_photo_gallery_chrome( array() );
		$this->assertStringContainsString( 'data-filter="*"', $html ); // All
		$this->assertStringContainsString( 'data-filter="casa-galbiga"', $html );
		$this->assertStringContainsString( 'data-filter="casa-tremezzo"', $html );
	}

	public function test_orders_photos_by_menu_order() {
		$this->make_photo( 'Second', 'casa-galbiga', 20 );
		$this->make_photo( 'First', 'casa-galbiga', 10 );
		$html = pediment_child_photo_gallery_chrome( array() );
		$this->assertLessThan(
			strpos( $html, 'Second' ),
			strpos( $html, 'First' ),
			'Lower menu_order should render first'
		);
	}

	public function test_uses_headline_attribute() {
		$this->make_photo( 'A', 'casa-galbiga', 10 );
		$html = pediment_child_photo_gallery_chrome( array( 'headline' => 'Our spaces' ) );
		$this->assertStringContainsString( 'Our spaces', $html );
	}
}
