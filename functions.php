<?php
/**
 * Workation Castle theme bootstrap.
 *
 * A standalone Pediment client theme. The Pediment plugin ships the design
 * system, the shared blocks, the templates and the seeding engine; what lives
 * here is what is specific to this client — 23 bespoke blocks under
 * src/blocks/, the guest check-in flow, the photo and activity post types, and
 * the stylesheet. Site structure is declared in seed/manifest.php.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WORKATION_DIR' ) ) {
	define( 'WORKATION_DIR', __DIR__ );
}
if ( ! defined( 'WORKATION_VERSION' ) ) {
	define( 'WORKATION_VERSION', '0.12.0' ); // Bumped on release by x-release-please-version.
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// GDPR consent manager: gates third-party embeds + analytics behind opt-in.
require_once __DIR__ . '/inc/Consent.php';

// Photo gallery: custom post type + taxonomy for the filterable /photos grid.
require_once __DIR__ . '/inc/Photos.php';

// Activities: public custom post type for the /activities/ page and its singles.
require_once __DIR__ . '/inc/Activities.php';

// Photo library + activity content, sideloaded from the theme's manifests.
// Must load after Photos.php and Activities.php, whose CPT/taxonomy constants
// it writes to. Pages, navigations and languages belong to the Pediment
// plugin's manifest engine; these two post types stay client-owned.
require_once __DIR__ . '/inc/CptContent.php';

// Check-in: private CPT + REST endpoint + Brevo email for guest registration.
require_once __DIR__ . '/inc/CheckIn.php';
\Workation\CheckIn::register();
require_once __DIR__ . '/inc/Brevo.php';

// Section block render helpers (also loaded by individual block render.php files,
// but required here so helpers are available outside the block rendering path,
// e.g. in unit tests and direct template includes).
require_once __DIR__ . '/inc/WorkationSections.php';
require_once __DIR__ . '/inc/EstateMap.php';
require_once __DIR__ . '/inc/AvailabilityForm.php';

// Section copy for pages stored before that copy moved into the pattern markup.
// Must load after WorkationSections.php, whose render helpers consume the
// attributes it supplies. Transitional — see the file header.
require_once __DIR__ . '/inc/LegacyBlockCopy.php';

// Legacy URL redirects: 301 retired paths (renamed/re-nested/removed pages) to
// their new homes so old inbound links and search results keep working.
require_once __DIR__ . '/inc/Redirects.php';
\Workation\Redirects::register();

// TEMPORARY: the cutover's one-shot block-namespace rewrite. Delete in the
// release after the cutover — see inc/NamespaceRewrite.php.
require_once __DIR__ . '/inc/NamespaceRewrite.php';
if ( is_admin() ) {
	Workation\NamespaceRewrite::register_admin();
}

/**
 * Register every block in the given directory (defaults to build/blocks).
 *
 * Prefixed rather than named generically: the Pediment plugin registers its own
 * blocks under its own prefix, and a shared name would collide the moment the
 * two ever loaded in the same request.
 *
 * @param string|null $base_dir Directory containing block subfolders.
 */
function workation_register_blocks( $base_dir = null ) {
	$is_default_dir = ( null === $base_dir || '' === $base_dir );
	if ( $is_default_dir ) {
		$base_dir = WORKATION_DIR . '/build/blocks';
	}

	if ( ! is_dir( $base_dir ) ) {
		// A missing build/blocks unregisters every block at once: the editor
		// reports each section as unsupported and the front end renders empty,
		// with nothing in the logs. Flag it so the cause is visible in wp-admin
		// rather than inferred from a blank page. Only the real build dir is
		// flagged; callers passing an explicit path (tests) are not.
		if ( $is_default_dir ) {
			workation_flag_missing_build();
		}
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
		workation_register_blocks();
	}
);

/**
 * Register the pattern category this theme's patterns declare.
 *
 * Previously done by the theme's own seeder, which the plugin's seeding engine
 * replaced. Patterns whose category is not registered still work, but they are
 * filed under "Uncategorized" in the inserter.
 */
function workation_register_pattern_category(): void {
	register_block_pattern_category(
		'workation',
		array( 'label' => __( 'Workation Castle', 'workation' ) )
	);
}
add_action( 'init', 'workation_register_pattern_category' );

/**
 * Record that the theme's built blocks are missing, and surface it in wp-admin.
 *
 * The usual cause is a theme package built without `npm run build`, or an
 * update that installed GitHub's auto-generated source zip (which excludes the
 * gitignored `build/`) instead of the release asset.
 */
function workation_flag_missing_build() {
	// Idempotent: init can fire more than once in CLI/eval contexts, and two
	// copies of the same notice help nobody.
	if ( has_action( 'admin_notices', 'workation_render_missing_build_notice' ) ) {
		return;
	}
	add_action( 'admin_notices', 'workation_render_missing_build_notice' );
}

/**
 * Render the "no blocks are registered" notice.
 *
 * Kept separate from workation_flag_missing_build() so the capability gate
 * is exercisable on its own.
 */
function workation_render_missing_build_notice() {
	if ( ! current_user_can( 'switch_themes' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'Workation Castle theme: no blocks are registered.', 'workation' ); ?></strong>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: absolute path to the theme's build/blocks directory. */
				esc_html__( 'The built block directory %s is missing, so every section block is unavailable and pages using them render empty. Reinstall the theme from the workation.zip release asset (not the "Source code" zip).', 'workation' ),
				'<code>' . esc_html( WORKATION_DIR . '/build/blocks' ) . '</code>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Retire the generic `pediment/cta` block the plugin ships.
 *
 * The site uses one closing call-to-action everywhere: the branded
 * `workation/workation-closing` section from the homepage bottom
 * (full-bleed image, headline, Check availability / Ask for a custom offer,
 * Instagram link). The plugin's plain `pediment/cta` band is off-brand, so it
 * is unregistered here to keep it out of the inserter and prevent accidental
 * reuse in new pages. Runs after registration (priority 20). No pattern ships
 * `wp:pediment/cta`, so nothing renders blank.
 */
add_action(
	'init',
	function () {
		$registry = WP_Block_Type_Registry::get_instance();
		if ( $registry->is_registered( 'pediment/cta' ) ) {
			unregister_block_type( 'pediment/cta' );
		}
	},
	20
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$style_path = get_stylesheet_directory() . '/style.css';
		wp_enqueue_style(
			'workation',
			get_stylesheet_directory_uri() . '/style.css',
			array(),
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
		wp_localize_script(
			'workation-castle-lightbox',
			'wcLightbox',
			array(
				'viewer' => __( 'Image viewer', 'workation' ),
				'close'  => __( 'Close', 'workation' ),
				'prev'   => __( 'Previous image', 'workation' ),
				'next'   => __( 'Next image', 'workation' ),
			)
		);

		$photo_filter_js_path = get_stylesheet_directory() . '/assets/js/photo-filter.js';
		wp_enqueue_script(
			'workation-castle-photo-filter',
			get_stylesheet_directory_uri() . '/assets/js/photo-filter.js',
			array(),
			file_exists( $photo_filter_js_path ) ? (string) filemtime( $photo_filter_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		$booking_newtab_js_path = get_stylesheet_directory() . '/assets/js/booking-newtab.js';
		wp_enqueue_script(
			'workation-castle-booking-newtab',
			get_stylesheet_directory_uri() . '/assets/js/booking-newtab.js',
			array(),
			file_exists( $booking_newtab_js_path ) ? (string) filemtime( $booking_newtab_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		$range_picker_js_path = get_stylesheet_directory() . '/assets/js/range-picker.js';
		wp_enqueue_script(
			'workation-castle-range-picker',
			get_stylesheet_directory_uri() . '/assets/js/range-picker.js',
			array(),
			file_exists( $range_picker_js_path ) ? (string) filemtime( $range_picker_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);
		wp_localize_script( 'workation-castle-range-picker', 'wcRangePicker', workation_range_picker_l10n() );

		$estate_map_js_path = get_stylesheet_directory() . '/assets/js/estate-map.js';
		wp_enqueue_script(
			'workation-castle-estate-map',
			get_stylesheet_directory_uri() . '/assets/js/estate-map.js',
			array(),
			file_exists( $estate_map_js_path ) ? (string) filemtime( $estate_map_js_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		// Activity locator maps (Leaflet) — only on single activity pages.
		if ( defined( 'WORKATION_ACTIVITY_CPT' ) && is_singular( WORKATION_ACTIVITY_CPT ) ) {
			wp_enqueue_style(
				'leaflet',
				'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
				array(),
				'1.9.4'
			);
			wp_enqueue_script(
				'leaflet',
				'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
				array(),
				'1.9.4',
				true
			);

			$activity_map_js_path = get_stylesheet_directory() . '/assets/js/activity-map.js';
			wp_enqueue_script(
				'workation-castle-activity-map',
				get_stylesheet_directory_uri() . '/assets/js/activity-map.js',
				array( 'leaflet' ),
				file_exists( $activity_map_js_path ) ? (string) filemtime( $activity_map_js_path ) : wp_get_theme()->get( 'Version' ),
				true
			);
			wp_localize_script(
				'workation-castle-activity-map',
				'wcActivityMap',
				array(
					'seeOnGoogleMaps' => __( 'See on Google Maps', 'workation' ),
				)
			);
		}
	}
);

/**
 * Load the child theme's translation catalog.
 *
 * The theme wraps its own UI strings in __(), but gettext only resolves them
 * once a catalog is bound to the domain — without this, every string falls
 * through to its English msgid in all five languages.
 *
 * Hooked on after_setup_theme so the catalog is present before init, which is
 * when the CPT labels and the check-in / consent payloads are built. Passing an
 * explicit path also registers it with WP_Textdomain_Registry, so Polylang's
 * per-request locale switch reloads the right catalog instead of keeping the
 * first one it loaded.
 *
 * @return void
 */
function workation_load_textdomain(): void {
	load_child_theme_textdomain( 'workation', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'workation_load_textdomain' );

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
function workation_favicon() {
	$href = get_theme_file_uri( 'assets/images/favicon.svg' );
	printf(
		'<link rel="icon" href="%1$s?v=%2$s" type="image/svg+xml">' . "\n",
		esc_url( $href ),
		esc_attr( WORKATION_VERSION )
	);
}
add_action( 'wp_head', 'workation_favicon' );
add_action( 'admin_head', 'workation_favicon' );
add_action( 'login_head', 'workation_favicon' );

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
function workation_body_class( $classes ) {
	$has_hero = false;
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$has_hero = has_block( 'workation/workation-hero', $post )
				|| has_block( 'workation/page-hero', $post );
		}
	}
	if ( ! $has_hero ) {
		$classes[] = 'no-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'workation_body_class' );

/**
 * Resolve the %WORKATION_THEME_URI% placeholder in static template parts.
 *
 * Template-part HTML files (header, footer) can't run PHP, so they reference
 * theme assets such as the brand logo through this token. Swapping it for the
 * real stylesheet URI at render time keeps the markup portable: the theme
 * directory is named after the deploy, never hard-coded.
 */
add_filter(
	'render_block_core/html',
	function ( $content ) {
		if ( false === strpos( $content, '%WORKATION_THEME_URI%' ) ) {
			return $content;
		}
		return str_replace(
			'%WORKATION_THEME_URI%',
			esc_url( get_stylesheet_directory_uri() ),
			$content
		);
	}
);
