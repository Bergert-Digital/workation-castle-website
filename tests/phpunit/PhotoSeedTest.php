<?php

class PhotoSeedTest extends WP_UnitTestCase {

	/** Serve the fixture file for any sideload HTTP request (no network). */
	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', array( $this, 'serve_fixture' ), 10, 3 );
	}

	public function serve_fixture( $pre, $args, $url ) {
		// download_url() streams to $args['filename']; write the fixture there.
		$body = file_get_contents( __DIR__ . '/fixtures/sample-photo.jpg' );
		if ( ! empty( $args['filename'] ) ) {
			file_put_contents( $args['filename'], $body );
		}
		return array(
			'headers'  => array(
				'content-type'        => 'image/jpeg',
				'content-disposition' => 'inline; filename=sample-photo.jpg',
			),
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'filename' => ! empty( $args['filename'] ) ? $args['filename'] : null,
			'body'     => '',
		);
	}

	private function manifest(): array {
		return array(
			array( 'url' => 'https://example.com/a.jpg', 'alt' => 'Garden', 'category' => 'casa-galbiga', 'order' => 10 ),
			array( 'url' => 'https://example.com/b.jpg', 'alt' => 'Kitchen', 'category' => 'casa-tremezzo', 'order' => 20 ),
		);
	}

	public function test_seed_terms_creates_categories() {
		\PedimentChild\Seed::seed_photo_terms();
		$this->assertNotFalse( get_term_by( 'slug', 'casa-galbiga', 'wc_photo_category' ) );
		$this->assertNotFalse( get_term_by( 'slug', 'casa-tremezzo', 'wc_photo_category' ) );
		$this->assertNotFalse( get_term_by( 'slug', 'workspace', 'wc_photo_category' ) );
		$this->assertNotFalse( get_term_by( 'slug', 'garden-castle', 'wc_photo_category' ) );
		$this->assertNotFalse( get_term_by( 'slug', 'surroundings', 'wc_photo_category' ) );
	}

	public function test_seed_photos_creates_posts_with_thumbnail_and_term() {
		\PedimentChild\Seed::seed_photo_terms();
		\PedimentChild\Seed::seed_photos( $this->manifest() );

		$photos = get_posts( array( 'post_type' => 'wc_photo', 'numberposts' => -1 ) );
		$this->assertCount( 2, $photos );

		$one = $photos[0];
		$this->assertTrue( has_post_thumbnail( $one->ID ) );
		$this->assertNotEmpty( wp_get_object_terms( $one->ID, 'wc_photo_category' ) );
	}

	public function test_seed_photos_is_idempotent() {
		\PedimentChild\Seed::seed_photo_terms();
		\PedimentChild\Seed::seed_photos( $this->manifest() );
		\PedimentChild\Seed::seed_photos( $this->manifest() );
		$photos = get_posts( array( 'post_type' => 'wc_photo', 'numberposts' => -1 ) );
		$this->assertCount( 2, $photos, 'Re-running must not duplicate photos' );
	}
}
