<?php

class ReviewsSeedTest extends WP_UnitTestCase {

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

	public function test_reviews_is_registered_in_seed_pages() {
		$this->assertArrayHasKey( 'reviews', \PedimentChild\Seed::PAGES );
		$this->assertSame( 'patterns/reviews.php', \PedimentChild\Seed::PAGES['reviews']['pattern_file'] );
		$this->assertFileExists( dirname( __DIR__, 2 ) . '/patterns/reviews.php' );
	}

	public function test_seed_creates_published_reviews_page() {
		\PedimentChild\Seed::seed();
		$page = get_page_by_path( 'reviews' );
		$this->assertInstanceOf( \WP_Post::class, $page );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertSame( 'Reviews', $page->post_title );
		$this->assertStringContainsString( 'wp:pediment-child/workation-reviews', $page->post_content );
	}

	public function test_reviews_page_lists_all_fifteen_reviewers() {
		\PedimentChild\Seed::seed();
		$page  = get_page_by_path( 'reviews' );
		$names = array(
			'Eloisa R.', 'Kathrin K.', 'Nicole R.', 'Daniel V.', 'Simone S.',
			'Maria L.', 'Philippe R.', 'Alexandros P.', 'Anna-Lena F.', 'Alexander M.',
			'Manuelle B.', 'Liesbeth L.', 'Irma S.', 'Corinne O.', 'Ed K.',
		);
		foreach ( $names as $name ) {
			$this->assertStringContainsString( $name, $page->post_content );
		}
		$this->assertSame( 15, substr_count( $page->post_content, 'wp:pediment-child/workation-review ' ) );
	}
}
