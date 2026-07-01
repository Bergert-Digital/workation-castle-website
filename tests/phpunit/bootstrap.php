<?php
/**
 * PHPUnit bootstrap: loads WP test harness and the child theme.
 *
 * Runs inside wp-env's tests-wordpress container.
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills' );
}

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function () {
		// The child theme is mounted under its host directory's basename
		// (wp-env names the mount after the checkout folder), which varies
		// per Conductor workspace (madrid, accra, tacoma, …). Derive the slug
		// from this file's own location rather than a hardcoded allowlist, so
		// the harness loads the theme whatever the workspace is called.
		$theme_slug = basename( dirname( __DIR__, 2 ) );
		switch_theme( $theme_slug );
	}
);

require $_tests_dir . '/includes/bootstrap.php';
