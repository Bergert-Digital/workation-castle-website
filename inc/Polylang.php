<?php
/**
 * Polylang integration: what this theme needs Polylang to know.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register wp_navigation as a translatable post type.
 *
 * The header binds its menu with a language-scoped query (see inc/PrimaryNav.php),
 * which only yields a menu per language if Polylang treats wp_navigation as
 * translatable. This cannot be switched on by clicking: Polylang's settings screen
 * offers only post types registered with `public => true` and `_builtin => false`,
 * and wp_navigation is `public => false, _builtin => true`, so it never appears
 * there. Polylang carries no wp_navigation handling of its own — its menu
 * translation UI works on classic nav_menu terms, which a block theme does not use.
 *
 * Filtering when $is_settings is false uses Polylang's "programmatically active"
 * path: always on, and shown as a disabled checkbox rather than one a site owner
 * can untick and silently lose every translated menu to.
 *
 * @param string[] $post_types  Post types Polylang manages, keyed by post type name.
 * @param bool     $is_settings Whether the list is being built for the settings screen.
 * @return string[] Post types, including wp_navigation outside the settings screen.
 */
function pediment_child_translate_navigation_menus( $post_types, $is_settings ) {
	if ( ! $is_settings ) {
		$post_types['wp_navigation'] = 'wp_navigation';
	}
	return $post_types;
}
add_filter( 'pll_get_post_types', 'pediment_child_translate_navigation_menus', 10, 2 );

/**
 * Tag any seeded content that carries no Polylang language with the site's
 * default language.
 *
 * The seed inserts pages, the Primary menu, photos and activities via
 * wp_insert_post() directly, which Polylang never sees — none of that content
 * is tagged with a language unless something else does it. Untagged content
 * is invisible to pll_get_post(): pediment_child_seed_nav_translations() maps
 * translated menu items to real page permalinks by looking up
 * pll_get_post( $page_id, $lang ), and an untagged page returns nothing there
 * even when a linked translation exists, so every translated menu item
 * silently falls back to the default-language URL forever. An untagged
 * wp_navigation is worse: the header's language-scoped menu lookup (see
 * inc/PrimaryNav.php) misses it entirely, which has previously removed a live
 * site's header outright.
 *
 * Only ever tags a post with no language at all — an already-tagged post is
 * never re-tagged or moved — which is what makes this idempotent and safe to
 * run on every seed.
 *
 * @return string[] A single log line describing the outcome.
 */
function pediment_child_tag_untagged_content(): array {
	if ( ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_set_post_language' ) ) {
		return array( 'polylang tagging: Polylang inactive — skipped' );
	}

	$default = (string) pll_default_language();
	if ( '' === $default ) {
		return array( 'polylang tagging: no default language configured — skipped' );
	}

	$post_types = array( 'page', 'wp_navigation' );
	if ( defined( 'PEDIMENT_CHILD_ACTIVITY_CPT' ) ) {
		$post_types[] = PEDIMENT_CHILD_ACTIVITY_CPT;
	}
	if ( defined( 'PEDIMENT_CHILD_PHOTO_CPT' ) ) {
		$post_types[] = PEDIMENT_CHILD_PHOTO_CPT;
	}

	// suppress_filters is required here, not optional: Polylang otherwise
	// scopes this query to a single language, which is precisely the content
	// this needs to find never has — it would never see the untagged posts.
	$ids = get_posts(
		array(
			'post_type'        => $post_types,
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);

	$tagged = 0;
	foreach ( $ids as $id ) {
		$language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( (int) $id ) : false;
		if ( $language ) {
			continue;
		}
		pll_set_post_language( (int) $id, $default );
		++$tagged;
	}

	return array( sprintf( 'polylang tagging: tagged %d untagged object(s) as %s', $tagged, $default ) );
}
