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

// Photo gallery: custom post type + taxonomy for the filterable /photos grid.
require_once __DIR__ . '/inc/Photos.php';

// Activities: public custom post type for the /activities/ page and its singles.
require_once __DIR__ . '/inc/Activities.php';

// Section block render helpers (also loaded by individual block render.php files,
// but required here so helpers are available outside the block rendering path,
// e.g. in unit tests and direct template includes).
require_once __DIR__ . '/inc/WorkationSections.php';

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

		$lightbox_js_path = get_stylesheet_directory() . '/assets/js/lightbox.js';
		wp_enqueue_script(
			'workation-castle-lightbox',
			get_stylesheet_directory_uri() . '/assets/js/lightbox.js',
			array(),
			file_exists( $lightbox_js_path ) ? (string) filemtime( $lightbox_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		$photo_filter_js_path = get_stylesheet_directory() . '/assets/js/photo-filter.js';
		wp_enqueue_script(
			'workation-castle-photo-filter',
			get_stylesheet_directory_uri() . '/assets/js/photo-filter.js',
			array(),
			file_exists( $photo_filter_js_path ) ? (string) filemtime( $photo_filter_js_path ) : wp_get_theme()->get( 'Version' ),
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

/**
 * Flag pages that have no full-bleed hero behind the fixed header.
 *
 * The site header is designed to overlay a cinematic hero — white logo and nav
 * on a transparent background. On pages without such a hero (most inner pages,
 * archives, 404, search) that white-on-light is unreadable, and the content
 * slides up under the fixed header. This adds a `no-hero` body class that the
 * stylesheet keys both fixes off (solid header + top padding), so the behaviour
 * is generic and content-driven rather than configured per page. A page counts
 * as having a hero when it contains the workation-hero or page-hero block —
 * both render a full-bleed photo the transparent header can overlay.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function pediment_child_body_class( $classes ) {
	$has_hero = false;
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$has_hero = has_block( 'pediment-child/workation-hero', $post )
				|| has_block( 'pediment-child/page-hero', $post );
		}
	}
	if ( ! $has_hero ) {
		$classes[] = 'no-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'pediment_child_body_class' );

/**
 * Resolve the %PEDIMENT_CHILD_THEME_URI% placeholder in static template parts.
 *
 * Template-part HTML files (header, footer) can't run PHP, so they reference
 * theme assets such as the brand logo through this token. Swapping it for the
 * real stylesheet URI at render time keeps the markup portable: the theme
 * directory is named after the deploy, never hard-coded.
 */
add_filter(
	'render_block_core/html',
	function ( $content ) {
		if ( false === strpos( $content, '%PEDIMENT_CHILD_THEME_URI%' ) ) {
			return $content;
		}
		return str_replace(
			'%PEDIMENT_CHILD_THEME_URI%',
			esc_url( get_stylesheet_directory_uri() ),
			$content
		);
	}
);

/**
 * Keep the theme-file `header` template part canonical.
 *
 * The parent theme's seed (pediment_seed_header_template_part) unconditionally
 * inserts an editable, DB-backed `header` wp_template_part, which otherwise
 * shadows this child's parts/header.html — the branded header with the
 * transparent-on-scroll behaviour. The parent exposes no hook to skip that
 * step, so instead of deleting DB rows (which a re-seed would recreate) we
 * hide the DB copy from template-part resolution. parts/header.html then wins
 * in every environment, surviving re-seeds and stray Site Editor edits.
 *
 * Two paths resolve a template part, so both are intercepted:
 *  - the front end (render_block_core_template_part) runs its own WP_Query
 *    keyed on post_name__in — caught via `posts_pre_query`;
 *  - get_block_template() (REST / editor single fetch) — caught via
 *    `pre_get_block_template`.
 */
add_filter(
	'posts_pre_query',
	function ( $posts, $query ) {
		if ( 'wp_template_part' !== $query->get( 'post_type' ) ) {
			return $posts;
		}
		if ( in_array( 'header', (array) $query->get( 'post_name__in' ), true ) ) {
			return array(); // No DB row -> core falls back to parts/header.html.
		}
		return $posts;
	},
	10,
	2
);

add_filter(
	'pre_get_block_template',
	function ( $block_template, $id, $template_type ) {
		if ( 'wp_template_part' !== $template_type ) {
			return $block_template;
		}
		$parts = explode( '//', $id, 2 );
		if ( count( $parts ) < 2 || get_stylesheet() !== $parts[0] || 'header' !== $parts[1] ) {
			return $block_template;
		}
		$file_template = get_block_file_template( $id, $template_type );
		return $file_template ? $file_template : $block_template;
	},
	10,
	3
);

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
