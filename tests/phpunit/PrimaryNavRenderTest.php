<?php

class PrimaryNavRenderTest extends WP_UnitTestCase {

	private function make_primary_menu(): int {
		return wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_title'   => 'Primary',
				'post_name'    => 'primary',
				'post_status'  => 'publish',
				'post_content' => pediment_child_primary_nav_blocks(),
			)
		);
	}

	private function nav_block( array $attrs = array(), array $inner = array(), array $inner_content = array() ): array {
		return array(
			'blockName'    => 'core/navigation',
			'attrs'        => $attrs,
			'innerBlocks'  => $inner,
			'innerHTML'    => '',
			'innerContent' => $inner_content,
		);
	}

	public function test_injects_primary_menu_ref_onto_refless_navigation() {
		$id  = $this->make_primary_menu();
		$out = pediment_child_inject_primary_nav_ref( $this->nav_block() );
		$this->assertSame( $id, $out['attrs']['ref'] );
	}

	public function test_leaves_explicitly_referenced_navigation_untouched() {
		$this->make_primary_menu();
		$out = pediment_child_inject_primary_nav_ref( $this->nav_block( array( 'ref' => 999 ) ) );
		$this->assertSame( 999, $out['attrs']['ref'] );
	}

	public function test_leaves_navigation_unchanged_when_no_primary_menu() {
		$inner         = array( array( 'blockName' => 'core/page-list' ) );
		$inner_content = array( 'x' );
		$out           = pediment_child_inject_primary_nav_ref(
			$this->nav_block( array(), $inner, $inner_content )
		);
		$this->assertArrayNotHasKey( 'ref', $out['attrs'] );
		$this->assertSame( $inner, $out['innerBlocks'] );
		$this->assertSame( $inner_content, $out['innerContent'] );
	}

	public function test_ignores_non_navigation_blocks() {
		$block = array( 'blockName' => 'core/paragraph', 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() );
		$this->assertSame( $block, pediment_child_inject_primary_nav_ref( $block ) );
	}

	public function test_absent_primary_menu_renders_nothing_and_creates_no_stray_menu() {
		// No primary menu exists.
		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );
		$this->assertStringNotContainsString( 'wp-block-page-list', $html );
		$this->assertSame( '', trim( $html ) );
		$stray = get_posts( array( 'post_type' => 'wp_navigation', 'post_status' => 'any', 'numberposts' => -1 ) );
		$this->assertCount( 0, $stray, 'No stray wp_navigation should be auto-created' );
	}

	public function test_seeded_primary_menu_renders_expected_links() {
		$this->make_primary_menu();
		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );
		$this->assertNotSame( '', trim( $html ) );
		$this->assertStringContainsString( 'Activities', $html );
	}
}
