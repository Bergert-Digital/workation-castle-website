<?php
/**
 * Primary navigation menu: canonical definition + Site-Editor wiring.
 *
 * The header renders a core/navigation block whose items live in an editable
 * `wp_navigation` post ("Primary", slug `primary`). This file holds the
 * version-controlled starter menu used by the content seed (create-if-absent).
 * The render-time ref-injection filter is added in a later task.
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
