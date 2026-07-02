<?php
/**
 * Primary navigation menu: canonical definition + Site-Editor wiring.
 *
 * The header renders a core/navigation block whose items live in an editable
 * `wp_navigation` post ("Primary", slug `primary`). This file holds the
 * version-controlled starter menu used by the content seed (create-if-absent),
 * plus a render-time filter that binds the header's ref-less navigation block to
 * that menu by slug (post IDs differ per environment, so the file can't hardcode one).
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical Primary-menu block markup (core/navigation inner blocks).
 *
 * Single source of truth for the seeded menu; mirrors the header links.
 *
 * @return string Serialized block markup for a wp_navigation post_content.
 */
function pediment_child_primary_nav_blocks(): string {
	return implode(
		"\n",
		array(
			'<!-- wp:navigation-link {"label":"Activities","url":"/activities/","kind":"custom","isTopLevelLink":true} /-->',
			'<!-- wp:navigation-link {"label":"Photos","url":"/photos/","kind":"custom","isTopLevelLink":true} /-->',
			'<!-- wp:navigation-submenu {"label":"Ways to stay","url":"/ways-to-stay/","kind":"custom","isTopLevelItem":true} -->',
			'<!-- wp:navigation-link {"label":"Team retreats","url":"/ways-to-stay/team-retreats/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"Workations","url":"/ways-to-stay/workations/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"Family & group stays","url":"/ways-to-stay/family-and-groups/","kind":"custom"} /-->',
			'<!-- /wp:navigation-submenu -->',
			'<!-- wp:navigation-submenu {"label":"Guest Guide","url":"/guide/","kind":"custom","isTopLevelItem":true} -->',
			'<!-- wp:navigation-link {"label":"How to get here","url":"/guide/arrival/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"Checking in","url":"/check-in/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"Find your way around","url":"https://workationcastle.com/guide/map/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"Sorting the waste","url":"https://workationcastle.com/guide/waste-disposal/","kind":"custom"} /-->',
			'<!-- /wp:navigation-submenu -->',
		)
	);
}

/**
 * The published "Primary" navigation menu, or null if it hasn't been seeded.
 *
 * @return WP_Post|null
 */
function pediment_child_get_primary_nav_menu() {
	$menu = get_posts(
		array(
			'post_type'        => 'wp_navigation',
			'name'             => 'primary',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);
	return $menu ? $menu[0] : null;
}

/**
 * Bind the header's Primary menu by slug at render time.
 *
 * The file-based header template part cannot hardcode a wp_navigation post ID
 * (IDs differ per environment / after re-seed). The header ships a ref-less
 * core/navigation block; this resolves the "primary" menu's ID and injects it.
 * When no such menu exists, the block is returned unchanged; see
 * pediment_child_suppress_navigation_without_menu() for what happens at render
 * time in that case. Scoped to ref-less navigation blocks so an
 * explicitly-referenced menu elsewhere is left alone.
 *
 * @param array $block Parsed block (render_block_data).
 * @return array
 */
function pediment_child_inject_primary_nav_ref( array $block ): array {
	if ( 'core/navigation' !== ( isset( $block['blockName'] ) ? $block['blockName'] : '' ) ) {
		return $block;
	}
	if ( ! empty( $block['attrs']['ref'] ) ) {
		return $block;
	}
	$menu = pediment_child_get_primary_nav_menu();
	if ( $menu ) {
		$block['attrs']['ref'] = (int) $menu->ID;
	}
	return $block;
}
add_filter( 'render_block_data', 'pediment_child_inject_primary_nav_ref' );

/**
 * Render nothing for the header's ref-less navigation when the Primary menu is
 * absent (e.g. a fresh site before the seed runs). Emptying inner blocks is not
 * enough: core's navigation renderer treats an empty ref-less block as a cue to
 * emit the all-pages Page List fallback (and persist a stray wp_navigation
 * post). Short-circuiting before render avoids both.
 *
 * @param string|null $pre   Short-circuit output (null to continue rendering).
 * @param array       $block Parsed block.
 * @return string|null
 */
function pediment_child_suppress_navigation_without_menu( $pre, array $block ) {
	if ( null !== $pre ) {
		return $pre;
	}
	if ( 'core/navigation' !== ( isset( $block['blockName'] ) ? $block['blockName'] : '' ) ) {
		return $pre;
	}
	if ( ! empty( $block['attrs']['ref'] ) ) {
		return $pre;
	}
	if ( pediment_child_get_primary_nav_menu() ) {
		return $pre;
	}
	return '';
}
add_filter( 'pre_render_block', 'pediment_child_suppress_navigation_without_menu', 10, 2 );
