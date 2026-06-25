<?php
/**
 * Pediment Child Theme bootstrap.
 *
 * Fork target. Pediment (parent) is read-only; your blocks,
 * theme.json overrides and child-specific PHP live here.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PEDIMENT_CHILD_DIR' ) ) {
	define( 'PEDIMENT_CHILD_DIR', __DIR__ );
}
if ( ! defined( 'PEDIMENT_CHILD_VERSION' ) ) {
	define( 'PEDIMENT_CHILD_VERSION', '0.1.0' );
}

// One-click theme updates from GitHub Releases (no manual zip uploads).
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/inc/ThemeUpdater.php';
\PedimentChild\ThemeUpdater::register();

// Content seed: rebuild pages from theme block patterns (`wp pediment-child seed`
// or Tools → Seed content). Keeps the homepage in version control, not just the DB.
require_once __DIR__ . '/inc/seed.php';

/**
 * Register every block in the given directory (defaults to build/blocks).
 *
 * Named distinctly from the parent's pediment_register_blocks() — both
 * functions.php files load for a child theme, so an identical name would
 * fatal-redeclare.
 *
 * @param string|null $base_dir Directory containing block subfolders.
 */
function pediment_child_register_blocks( $base_dir = null ) {
	if ( null === $base_dir || '' === $base_dir ) {
		$base_dir = PEDIMENT_CHILD_DIR . '/build/blocks';
	}

	if ( ! is_dir( $base_dir ) ) {
		return;
	}

	$registry = WP_Block_Type_Registry::get_instance();
	foreach ( glob( $base_dir . '/*', GLOB_ONLYDIR ) as $block_dir ) {
		$manifest = $block_dir . '/block.json';
		if ( ! file_exists( $manifest ) ) {
			continue;
		}
		$meta = json_decode( file_get_contents( $manifest ), true );
		if ( is_array( $meta ) && isset( $meta['name'] ) && $registry->is_registered( $meta['name'] ) ) {
			continue;
		}
		register_block_type( $block_dir );
	}
}

add_action(
	'init',
	function () {
		pediment_child_register_blocks();
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'workation-castle-fonts',
			'https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;400;700&family=Inria+Sans:wght@300;400;700&display=swap',
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts CSS2 uses repeated family params; WP versioning collapses them.
			null
		);

		$style_path = get_stylesheet_directory() . '/style.css';
		wp_enqueue_style(
			'pediment-child',
			get_stylesheet_directory_uri() . '/style.css',
			array( 'workation-castle-fonts' ),
			file_exists( $style_path ) ? (string) filemtime( $style_path ) : wp_get_theme()->get( 'Version' )
		);

		$header_js_path = get_stylesheet_directory() . '/assets/js/header.js';
		wp_enqueue_script(
			'workation-castle-header',
			get_stylesheet_directory_uri() . '/assets/js/header.js',
			array(),
			file_exists( $header_js_path ) ? (string) filemtime( $header_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		$reveal_js_path = get_stylesheet_directory() . '/assets/js/reveal.js';
		wp_enqueue_script(
			'workation-castle-reveal',
			get_stylesheet_directory_uri() . '/assets/js/reveal.js',
			array(),
			file_exists( $reveal_js_path ) ? (string) filemtime( $reveal_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);
	}
);

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'editor-styles' );
		add_editor_style( 'style.css' );
	}
);

/**
 * Mark the document as JS-enabled before paint so entrance animations can hide
 * their targets without a flash of content. Printed early in <head>; the
 * reveal stylesheet only hides elements under `html.js`, and reveal.js removes
 * the class again if it can't animate (no IntersectionObserver / reduced motion).
 */
add_action(
	'wp_head',
	function () {
		echo "<script>document.documentElement.classList.add('js');</script>\n";
	},
	1
);

/**
 * Output the site favicon (the Workation Castle logo mark).
 *
 * Emitted on the front end, the admin and the login screen so the brand mark
 * shows everywhere. Uses the SVG mark, which scales crisply at any size.
 */
function pediment_child_favicon() {
	$href = get_theme_file_uri( 'assets/images/favicon.svg' );
	printf(
		'<link rel="icon" href="%1$s?v=%2$s" type="image/svg+xml">' . "\n",
		esc_url( $href ),
		esc_attr( PEDIMENT_CHILD_VERSION )
	);
}
add_action( 'wp_head', 'pediment_child_favicon' );
add_action( 'admin_head', 'pediment_child_favicon' );
add_action( 'login_head', 'pediment_child_favicon' );

add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_style(
			'workation-castle-editor-fonts',
			'https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;400;700&family=Inria+Sans:wght@300;400;700&display=swap',
			array(),
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Google Fonts CSS2 uses repeated family params; WP versioning collapses them.
			null
		);
	}
);
