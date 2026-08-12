<?php
/**
 * Polylang integration for client-owned content libraries.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register client-owned post types as translatable.
 *
 * @param string[] $post_types  Post types Polylang manages, keyed by post type name.
 * @param bool     $is_settings Whether the list is being built for the settings screen.
 * @return string[] Post types, including client-owned types outside the settings screen.
 */
function workation_polylang_translate_client_post_types( $post_types, $is_settings ) {
	if ( ! $is_settings ) {
		$post_types[ WORKATION_ACTIVITY_CPT ] = WORKATION_ACTIVITY_CPT;
		$post_types[ WORKATION_PHOTO_CPT ]    = WORKATION_PHOTO_CPT;
	}

	return $post_types;
}
add_filter( 'pll_get_post_types', 'workation_polylang_translate_client_post_types', 10, 2 );

/**
 * Register the client-owned photo taxonomy as translatable.
 *
 * @param string[] $taxonomies  Taxonomies Polylang manages, keyed by taxonomy name.
 * @param bool     $is_settings Whether the list is being built for the settings screen.
 * @return string[] Taxonomies, including the photo taxonomy outside the settings screen.
 */
function workation_polylang_translate_client_taxonomies( $taxonomies, $is_settings ) {
	if ( ! $is_settings ) {
		$taxonomies[ WORKATION_PHOTO_TAX ] = WORKATION_PHOTO_TAX;
	}

	return $taxonomies;
}
add_filter( 'pll_get_taxonomies', 'workation_polylang_translate_client_taxonomies', 10, 2 );
