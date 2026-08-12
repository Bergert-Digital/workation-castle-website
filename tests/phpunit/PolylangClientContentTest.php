<?php
/**
 * Tests the client-owned content types Polylang integration.
 *
 * @package Workation
 */

class PolylangClientContentTest extends WP_UnitTestCase {

	public function test_client_content_is_managed_by_polylang_at_runtime() {
		$post_types = apply_filters( 'pll_get_post_types', array(), false );
		$taxonomies = apply_filters( 'pll_get_taxonomies', array(), false );

		$this->assertArrayHasKey( WORKATION_ACTIVITY_CPT, $post_types );
		$this->assertArrayHasKey( WORKATION_PHOTO_CPT, $post_types );
		$this->assertArrayHasKey( WORKATION_PHOTO_TAX, $taxonomies );
	}
}
