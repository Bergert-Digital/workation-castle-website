<?php

class MapSeedTest extends WP_UnitTestCase {

	public function test_map_is_registered_under_guide() {
		$this->assertArrayHasKey( 'map', \PedimentChild\Seed::PAGES );
		$this->assertSame( 'patterns/map.php', \PedimentChild\Seed::PAGES['map']['pattern_file'] );
		$this->assertSame( 'guide', \PedimentChild\Seed::PAGES['map']['parent'] );
		$this->assertFileExists( dirname( __DIR__, 2 ) . '/patterns/map.php' );
	}

	public function test_seed_creates_map_page_with_estate_block() {
		\PedimentChild\Seed::seed();
		$page = get_page_by_path( 'guide/map' );
		$this->assertInstanceOf( \WP_Post::class, $page );
		$this->assertSame( 'publish', $page->post_status );
		$this->assertStringContainsString( 'wp:pediment-child/estate-map', $page->post_content );
	}

	public function test_guide_card_links_internally() {
		$guide = file_get_contents( dirname( __DIR__, 2 ) . '/patterns/guide.php' );
		$this->assertStringContainsString( '"linkUrl":"/guide/map/"', $guide );
		$this->assertStringNotContainsString( 'workationcastle.com/guide/map/', $guide );
	}
}
