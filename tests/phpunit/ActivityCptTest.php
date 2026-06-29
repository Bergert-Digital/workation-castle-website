<?php

class ActivityCptTest extends WP_UnitTestCase {

	public function test_activity_cpt_is_registered() {
		$this->assertTrue( post_type_exists( 'wc_activity' ) );
	}

	public function test_activity_cpt_is_public_with_single_pages() {
		$obj = get_post_type_object( 'wc_activity' );
		$this->assertTrue( $obj->public );
		$this->assertTrue( $obj->publicly_queryable );
		$this->assertSame( 'activities', $obj->rewrite['slug'] );
	}

	public function test_activity_cpt_has_no_archive() {
		$obj = get_post_type_object( 'wc_activity' );
		$this->assertFalse( $obj->has_archive );
	}

	public function test_activity_cpt_supports_content_and_thumbnail() {
		$this->assertTrue( post_type_supports( 'wc_activity', 'title' ) );
		$this->assertTrue( post_type_supports( 'wc_activity', 'editor' ) );
		$this->assertTrue( post_type_supports( 'wc_activity', 'thumbnail' ) );
		$this->assertTrue( post_type_supports( 'wc_activity', 'excerpt' ) );
		$this->assertTrue( post_type_supports( 'wc_activity', 'page-attributes' ) );
	}

	public function test_activity_cpt_is_in_rest() {
		$obj = get_post_type_object( 'wc_activity' );
		$this->assertTrue( $obj->show_in_rest );
	}

	public function test_no_activity_taxonomy() {
		$this->assertFalse( taxonomy_exists( 'wc_activity_category' ) );
	}
}
