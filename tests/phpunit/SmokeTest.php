<?php

class SmokeTest extends WP_UnitTestCase {
	public function test_wordpress_is_loaded() {
		$this->assertTrue( function_exists( 'wp_get_theme' ) );
	}

	/**
	 * Identify the theme by its text domain, not its directory name.
	 *
	 * wp-env mounts the theme under its checkout folder's basename, which is
	 * whatever the Conductor workspace happens to be called. A hardcoded slug
	 * allowlist therefore fails locally in every workspace nobody remembered to
	 * add to it, while passing in CI — the worst of both.
	 */
	public function test_this_theme_is_active() {
		$this->assertSame( 'workation', wp_get_theme()->get( 'TextDomain' ) );
	}

	/**
	 * The theme is standalone: a Pediment *client* theme, not a child of the
	 * Pediment theme. Everything it used to inherit ships in the plugin now, and
	 * a stray `Template:` header would make WordPress hunt for a parent that is
	 * not installed.
	 */
	public function test_the_theme_declares_no_parent() {
		$this->assertSame( wp_get_theme()->get_stylesheet(), wp_get_theme()->get_template() );
		$this->assertStringNotContainsString(
			'Template:',
			file_get_contents( get_stylesheet_directory() . '/style.css' )
		);
	}
}
