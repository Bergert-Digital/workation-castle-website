<?php

class PhotoCptTest extends WP_UnitTestCase {

	public function test_photo_cpt_is_registered() {
		$this->assertTrue( post_type_exists( 'wc_photo' ) );
	}

	public function test_photo_cpt_supports_thumbnail_and_order() {
		$this->assertTrue( post_type_supports( 'wc_photo', 'thumbnail' ) );
		$this->assertTrue( post_type_supports( 'wc_photo', 'page-attributes' ) );
	}

	public function test_photo_cpt_is_in_rest() {
		$obj = get_post_type_object( 'wc_photo' );
		$this->assertTrue( $obj->show_in_rest );
	}

	public function test_photo_category_taxonomy_is_registered() {
		$this->assertTrue( taxonomy_exists( 'wc_photo_category' ) );
		$this->assertTrue( is_object_in_taxonomy( 'wc_photo', 'wc_photo_category' ) );
	}

	public function test_photo_category_is_hierarchical_and_in_rest() {
		$tax = get_taxonomy( 'wc_photo_category' );
		$this->assertTrue( $tax->hierarchical );
		$this->assertTrue( $tax->show_in_rest );
	}
}
