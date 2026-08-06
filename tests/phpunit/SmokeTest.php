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
		$this->assertSame( 'pediment-child', wp_get_theme()->get( 'TextDomain' ) );
	}

	public function test_parent_template_is_pediment() {
		$this->assertSame( 'pediment', wp_get_theme()->get_template() );
	}
}
