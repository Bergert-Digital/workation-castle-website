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
	define( 'WORKATION_VERSION', '1.6.3' ); // Bumped on release by x-release-please-version.
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

// Polylang: translate the client-owned activity and photo content libraries.
require_once __DIR__ . '/inc/Polylang.php';

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

// Renders the static footer template part: translatable copy, language-aware
// links and the Polylang switcher. See inc/Footer.php for why this can't live
// in parts/footer.html.
require_once __DIR__ . '/inc/Footer.php';

// Localize hardcoded root-relative internal links in body content to the
// current language at render time, so translated pages stop leaking to the
// English page. Mirrors the footer's link handling — see inc/LocalizeLinks.php.
require_once __DIR__ . '/inc/LocalizeLinks.php';

// Append the current language to every Holidu "Check availability" booking
// link so the booking flow opens in the visitor's language. See inc/Booking.php.
require_once __DIR__ . '/inc/Booking.php';

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

// GitHub-release auto-updates: offer one-click theme updates from this repo's
// releases (public repo, no token). No-ops without the Pediment plugin's
// bundled Plugin Update Checker and in local/dev — see inc/ThemeUpdater.php.
require_once __DIR__ . '/inc/ThemeUpdater.php';
\Workation\ThemeUpdater::register();

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
 * Retire the generic plugin blocks that duplicate a branded theme section.
 *
 * Each of these ships a plain, off-brand version of a section the theme already
 * provides, so leaving both registered puts two look-alike blocks in the
 * inserter and invites picking the wrong one (e.g. `pediment/hero` renders dark
 * text on a light band with no eyebrow, unlike the theme's image-backed hero).
 * Unregistering the generics keeps a single obvious choice per section:
 *
 *   - `pediment/cta`  → use `workation/workation-closing` (the closing CTA).
 *   - `pediment/hero` → use `workation/page-hero` (sub-pages) or
 *                       `workation/workation-hero` (homepage).
 *
 * Runs after registration (priority 20). No pattern ships these generics, so
 * nothing renders blank.
 */
add_action(
	'init',
	function () {
		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( array( 'pediment/cta', 'pediment/hero' ) as $block ) {
			if ( $registry->is_registered( $block ) ) {
				unregister_block_type( $block );
			}
		}
	},
	20
);

/**
 * Alignment styles for the parent's feature card.
 *
 * The theme restyles `pediment/feature` to left-aligned content globally (see
 * style.css), overriding the plugin's centred default. These block styles put
 * that choice in the editor's Styles panel per card: Left stays the default
 * (no class, so every existing card keeps rendering as before), Centered and
 * Right are opt-in via `is-style-align-center` / `is-style-align-right`,
 * which style.css styles for both the icon tile and the text.
 */
add_action(
	'init',
	function () {
		register_block_style(
			'pediment/feature',
			array(
				'name'       => 'align-left',
				'label'      => __( 'Left aligned', 'workation' ),
				'is_default' => true,
			)
		);
		register_block_style(
			'pediment/feature',
			array(
				'name'  => 'align-center',
				'label' => __( 'Centered', 'workation' ),
			)
		);
		register_block_style(
			'pediment/feature',
			array(
				'name'  => 'align-right',
				'label' => __( 'Right aligned', 'workation' ),
			)
		);
	}
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
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$reveal_js_path = get_stylesheet_directory() . '/assets/js/reveal.js';
		wp_enqueue_script(
			'workation-castle-reveal',
			get_stylesheet_directory_uri() . '/assets/js/reveal.js',
			array(),
			file_exists( $reveal_js_path ) ? (string) filemtime( $reveal_js_path ) : wp_get_theme()->get( 'Version' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$lightbox_js_path = get_stylesheet_directory() . '/assets/js/lightbox.js';
		wp_enqueue_script(
			'workation-castle-lightbox',
			get_stylesheet_directory_uri() . '/assets/js/lightbox.js',
			array(),
			file_exists( $lightbox_js_path ) ? (string) filemtime( $lightbox_js_path ) : wp_get_theme()->get( 'Version' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
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
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$booking_newtab_js_path = get_stylesheet_directory() . '/assets/js/booking-newtab.js';
		wp_enqueue_script(
			'workation-castle-booking-newtab',
			get_stylesheet_directory_uri() . '/assets/js/booking-newtab.js',
			array(),
			file_exists( $booking_newtab_js_path ) ? (string) filemtime( $booking_newtab_js_path ) : wp_get_theme()->get( 'Version' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		$range_picker_js_path = get_stylesheet_directory() . '/assets/js/range-picker.js';
		wp_enqueue_script(
			'workation-castle-range-picker',
			get_stylesheet_directory_uri() . '/assets/js/range-picker.js',
			array(),
			file_exists( $range_picker_js_path ) ? (string) filemtime( $range_picker_js_path ) : wp_get_theme()->get( 'Version' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_localize_script( 'workation-castle-range-picker', 'wcRangePicker', workation_range_picker_l10n() );

		$estate_map_js_path = get_stylesheet_directory() . '/assets/js/estate-map.js';
		wp_enqueue_script(
			'workation-castle-estate-map',
			get_stylesheet_directory_uri() . '/assets/js/estate-map.js',
			array(),
			file_exists( $estate_map_js_path ) ? (string) filemtime( $estate_map_js_path ) : wp_get_theme()->get( 'Version' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
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
				array(
					'in_footer' => true,
					'strategy'  => 'defer',
				)
			);
			wp_localize_script(
				'workation-castle-activity-map',
				'wcActivityMap',
				array(
					'seeOnGoogleMaps' => __( 'See on Google Maps', 'workation' ),
				)
			);
		}

		// The parent Pediment theme enqueues its own reveal.js with no loading
		// strategy, leaving it the last render-blocking script on the page. Defer
		// it too (nothing depends on it) so no front-end script blocks parsing or
		// DOMContentLoaded — which is what gates the entrance-animation reveal and
		// the consent banner. Handle is a no-op if the parent ever renames it.
		wp_script_add_data( 'pediment-reveal', 'strategy', 'defer' );
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
 * Style the theme's block-editor sidebar controls.
 *
 * The editor canvas is covered by add_editor_style(), but InspectorControls
 * render in the outer editor document, which that stylesheet does not reach.
 * The shared UrlField (a core URLInput) sizes to its popover default width, so
 * it overflows the narrow settings sidebar unless constrained to 100%.
 *
 * @return void
 */
function workation_block_editor_ui_styles(): void {
	wp_register_style( 'workation-editor-ui', false, array(), '1' );
	wp_enqueue_style( 'workation-editor-ui' );
	wp_add_inline_style(
		'workation-editor-ui',
		'.wc-url-field{display:block;width:100%}'
		// URLInput uses a flex-based InputControl; every item in the chain keeps
		// min-width:auto by default, so it refuses to shrink and overflows the
		// narrow sidebar. Force the chain (and the input) to shrink to 100%.
		. '.wc-url-field .block-editor-url-input{display:block;width:100%;min-width:0}'
		. '.wc-url-field .components-input-base,'
		. '.wc-url-field .components-input-control__container{width:100%!important;min-width:0!important}'
		. '.wc-url-field .components-input-control__input{width:100%!important;min-width:0!important;box-sizing:border-box}'
	);
}
add_action( 'enqueue_block_editor_assets', 'workation_block_editor_ui_styles' );

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
 * Resolve the %WORKATION_THEME_URI% and %WORKATION_HOME_URL% placeholders in
 * static template parts.
 *
 * Template-part HTML files (header, footer) can't run PHP, so they reference
 * dynamic values through tokens resolved at render time:
 *
 * - %WORKATION_THEME_URI% keeps asset URLs (the brand logo) portable, since the
 *   theme directory is named after the deploy and never hard-coded.
 * - %WORKATION_HOME_URL% keeps the logo/home link language-aware. The header is
 *   a single template part shared across every Polylang language (parts are not
 *   translated), so a literal "/" would always point at the default-language
 *   home. home_url() is filtered by Polylang per request, so the token resolves
 *   to the current language's home instead.
 */
add_filter(
	'render_block_core/html',
	function ( $content ) {
		if ( false === strpos( $content, '%WORKATION_THEME_URI%' )
			&& false === strpos( $content, '%WORKATION_HOME_URL%' ) ) {
			return $content;
		}
		return strtr(
			$content,
			array(
				'%WORKATION_THEME_URI%' => esc_url( get_stylesheet_directory_uri() ),
				'%WORKATION_HOME_URL%'  => esc_url( home_url( '/' ) ),
			)
		);
	}
);
