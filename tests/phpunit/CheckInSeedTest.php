<?php
// tests/phpunit/CheckInSeedTest.php

class CheckInSeedTest extends WP_UnitTestCase {

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

	public function test_checkin_registered_in_seed_pages() {
		$this->assertArrayHasKey( 'check-in', \PedimentChild\Seed::PAGES );
		$this->assertSame( 'patterns/check-in.php', \PedimentChild\Seed::PAGES['check-in']['pattern_file'] );
		$this->assertFileExists( dirname( __DIR__, 2 ) . '/patterns/check-in.php' );
	}

	public function test_seed_creates_checkin_page_with_block() {
		\PedimentChild\Seed::seed();
		$page = get_page_by_path( 'check-in' );
		$this->assertInstanceOf( \WP_Post::class, $page );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertStringContainsString( 'wp:pediment-child/check-in-form', $page->post_content );
	}
}
