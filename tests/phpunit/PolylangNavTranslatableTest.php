<?php

class PolylangNavTranslatableTest extends WP_UnitTestCase {

	public function test_navigation_is_translatable_outside_the_settings_screen() {
		$types = pediment_child_translate_navigation_menus( array(), false );
		$this->assertArrayHasKey( 'wp_navigation', $types );
		$this->assertSame( 'wp_navigation', $types['wp_navigation'] );
	}

	/**
	 * Polylang's settings screen lists only post types registered with
	 * public => true and _builtin => false, which wp_navigation is not. Adding it
	 * to the settings list would render a checkbox Polylang cannot honour.
	 */
	public function test_navigation_is_not_offered_as_a_settings_checkbox() {
		$types = pediment_child_translate_navigation_menus( array(), true );
		$this->assertArrayNotHasKey( 'wp_navigation', $types );
	}

	public function test_other_post_types_are_preserved() {
		$types = pediment_child_translate_navigation_menus( array( 'page' => 'page' ), false );
		$this->assertSame( 'page', $types['page'] );
		$this->assertArrayHasKey( 'wp_navigation', $types );
	}

	public function test_filter_is_registered() {
		$this->assertSame(
			10,
			has_filter( 'pll_get_post_types', 'pediment_child_translate_navigation_menus' )
		);
	}

	/**
	 * Polylang is not installed in the PHPUnit environment, so this is the
	 * only branch of pediment_child_tag_untagged_content() reachable here:
	 * with pll_default_language()/pll_set_post_language() both absent, it must
	 * report the guard and touch nothing.
	 */
	public function test_tag_untagged_content_is_a_noop_without_polylang() {
		$this->assertFalse( function_exists( 'pll_default_language' ) );
		$this->assertFalse( function_exists( 'pll_set_post_language' ) );

		$log = pediment_child_tag_untagged_content();

		$this->assertSame( array( 'polylang tagging: Polylang inactive — skipped' ), $log );
	}
}
