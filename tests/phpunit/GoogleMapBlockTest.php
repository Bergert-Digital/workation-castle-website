<?php

class GoogleMapBlockTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Allow get_block_wrapper_attributes() to run outside a block render context.
		WP_Block_Supports::$block_to_render = array( 'blockName' => '', 'attrs' => array() );
	}

	public function tearDown(): void {
		WP_Block_Supports::$block_to_render = null;
		parent::tearDown();
	}

	public function test_renders_a_titled_lazy_iframe_in_a_wc_map_figure() {
		$html = do_blocks( '<!-- wp:workation/google-map {"url":"https://maps.google.com/maps?daddr=46.03,9.14\\u0026hl=en\\u0026output=embed","title":"Map of the castle"} /-->' );
		$this->assertStringContainsString( '<figure class="wc-map wp-block-workation-google-map"', $html );
		$this->assertStringContainsString( 'title="Map of the castle"', $html );
		$this->assertStringContainsString( 'src="https://maps.google.com/maps?daddr=46.03,9.14&#038;hl=en&#038;output=embed"', $html );
		$this->assertStringContainsString( 'loading="lazy"', $html );
	}

	public function test_my_maps_embeds_render() {
		$html = workation_google_map_chrome( array( 'url' => 'https://www.google.com/maps/d/u/0/embed?mid=abc' ) );
		$this->assertStringContainsString( 'src="https://www.google.com/maps/d/u/0/embed?mid=abc"', $html );
	}

	/**
	 * @dataProvider not_google_maps
	 */
	public function test_other_urls_render_nothing( string $url ) {
		$this->assertSame( '', workation_google_map_chrome( array( 'url' => $url ) ) );
	}

	public function not_google_maps(): array {
		return array(
			'empty'          => array( '' ),
			'other host'     => array( 'https://example.com/maps/embed' ),
			'lookalike host' => array( 'https://maps.google.evil.example/maps' ),
			'suffix host'    => array( 'https://notgoogle.com/maps/x' ),
			'google search'  => array( 'https://www.google.com/search?q=maps' ),
			'plain http'     => array( 'http://maps.google.com/maps?output=embed' ),
		);
	}

	public function test_post_content_defuses_the_map_until_consent() {
		$html = apply_filters(
			'the_content',
			'<!-- wp:workation/google-map {"url":"https://www.google.com/maps/d/u/0/embed?mid=abc","title":"Waste points"} /-->'
		);
		$this->assertStringContainsString( 'data-consent-src="https://www.google.com/maps/d/u/0/embed?mid=abc"', $html );
		$this->assertStringNotContainsString( ' src="https://www.google.com', $html );
		$this->assertStringContainsString( 'Google Maps', $html );
	}
}
