<?php

class ActivitySeedTest extends WP_UnitTestCase {

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

	private function manifest(): array {
		return array(
			array(
				'source_url' => 'https://workationcastle.com/activities/alpha/',
				'slug'       => 'alpha',
				'title'      => 'Alpha Activity',
				'url'        => 'https://example.com/alpha.jpg',
				'alt'        => 'Alpha image',
				'excerpt'    => 'Short alpha blurb.',
				'order'      => 10,
				'content'    => '<!-- wp:paragraph --><p>Alpha body.</p><!-- /wp:paragraph -->',
			),
			array(
				'source_url' => 'https://workationcastle.com/activities/beta/',
				'slug'       => 'beta',
				'title'      => 'Beta Activity',
				'url'        => 'https://example.com/beta.jpg',
				'alt'        => 'Beta image',
				'excerpt'    => 'Short beta blurb.',
				'order'      => 20,
				'content'    => '<!-- wp:paragraph --><p>Beta body.</p><!-- /wp:paragraph -->',
			),
		);
	}

	public function test_seed_activities_creates_posts() {
		\Workation\CptContent::seed_activities( $this->manifest() );
		$posts = get_posts( array( 'post_type' => 'wc_activity', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) );
		$this->assertCount( 2, $posts );
		$this->assertSame( 'Alpha Activity', $posts[0]->post_title );
		$this->assertSame( 'alpha', $posts[0]->post_name );
		$this->assertSame( 10, (int) $posts[0]->menu_order );
		$this->assertStringContainsString( 'Alpha body.', $posts[0]->post_content );
		$this->assertSame( 'Short alpha blurb.', $posts[0]->post_excerpt );
		$this->assertTrue( has_post_thumbnail( $posts[0]->ID ) );
		$this->assertSame( 'https://workationcastle.com/activities/alpha/', get_post_meta( $posts[0]->ID, '_wc_activity_source_url', true ) );
	}

	public function test_seed_activities_is_idempotent() {
		\Workation\CptContent::seed_activities( $this->manifest() );
		\Workation\CptContent::seed_activities( $this->manifest() );
		$posts = get_posts( array( 'post_type' => 'wc_activity', 'numberposts' => -1 ) );
		$this->assertCount( 2, $posts );
	}

	public function test_activities_manifest_reads_file() {
		$manifest = \Workation\CptContent::activities_manifest();
		$this->assertNotEmpty( $manifest );
		$this->assertArrayHasKey( 'slug', $manifest[0] );
	}
}
