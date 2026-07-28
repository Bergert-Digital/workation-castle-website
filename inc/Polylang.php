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
