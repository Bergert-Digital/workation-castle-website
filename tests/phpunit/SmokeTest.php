<?php

class SmokeTest extends WP_UnitTestCase {
	public function test_wordpress_is_loaded() {
		$this->assertTrue( function_exists( 'wp_get_theme' ) );
	}

	public function test_child_theme_is_active() {
		$this->assertContains( wp_get_theme()->get_stylesheet(), array( 'pediment-child-theme', 'accra' ) );
	}

	public function test_parent_template_is_pediment() {
		$this->assertSame( 'pediment', wp_get_theme()->get_template() );
	}
}
