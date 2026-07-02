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
 * Bind the header's Primary menu by slug at render time.
 *
 * The file-based header template part cannot hardcode a wp_navigation post ID
 * (IDs differ per environment / after re-seed). The header ships a ref-less
 * core/navigation block; this resolves the "primary" menu's ID and injects it.
 * When no such menu exists, the block is emptied so it renders nothing instead
 * of core's all-pages Page List fallback. Scoped to ref-less navigation blocks
 * so an explicitly-referenced menu elsewhere is left alone.
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
	$menu = get_posts(
		array(
			'post_type'        => 'wp_navigation',
			'name'             => 'primary',
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		)
	);
	if ( empty( $menu ) ) {
		$block['innerBlocks']  = array();
		$block['innerContent'] = array();
		return $block;
	}
	$block['attrs']['ref'] = (int) $menu[0]->ID;
	return $block;
}
add_filter( 'render_block_data', 'pediment_child_inject_primary_nav_ref' );
