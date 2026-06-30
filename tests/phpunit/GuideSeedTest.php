<?php

class GuideSeedTest extends WP_UnitTestCase {

	/** Serve a fixture for any sideload HTTP request (no network). */
	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', array( $this, 'serve_fixture' ), 10, 3 );
	}

	public function serve_fixture( $pre, $args, $url ) {
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

	public function test_guide_is_registered_in_seed_pages() {
		$this->assertArrayHasKey( 'guide', \PedimentChild\Seed::PAGES );
		$this->assertSame( 'patterns/guide.php', \PedimentChild\Seed::PAGES['guide']['pattern_file'] );
		$this->assertFileExists( dirname( __DIR__, 2 ) . '/patterns/guide.php' );
	}

	public function test_seed_creates_published_guide_page() {
		\PedimentChild\Seed::seed();
		$page = get_page_by_path( 'guide' );
		$this->assertInstanceOf( \WP_Post::class, $page );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertSame( 'Guide', $page->post_title );
		$this->assertStringContainsString( 'wp:pediment/feature-grid', $page->post_content );
	}
}
