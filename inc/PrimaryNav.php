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
 * Post meta stamped on the Primary menu.
 *
 * Identifying the menu by meta rather than by the slug `primary` is what makes
 * it un-squattable: WordPress keeps slugs unique, so a stray post holding
 * `primary` used to push every replacement to `primary-2`, where a slug lookup
 * could never find it and the header rendered nothing. Meta has no such
 * collision rule. Mirrors the parent template's nav-seed marker approach.
 */
const PEDIMENT_CHILD_PRIMARY_NAV_MARKER = '_pediment_child_primary_nav';

/**
 * Run a wp_navigation lookup, retrying without language scoping when it comes
 * back empty.
 *
 * Polylang and WPML scope queries to the language being rendered, so a menu
 * tagged with a different language — or, on a site that predates the menus
 * becoming translatable, tagged with none at all — is invisible to the filtered
 * query. Because the header suppresses its navigation block when no menu is
 * found, that silently strips the site's whole navigation. Filtered runs first,
 * so a correctly-translated per-language menu still wins; the retry only decides
 * between the canonical menu and none at all.
 *
 * The retry needs both escape hatches, because the two plugins filter through
 * different doors:
 *
 * - `suppress_filters` covers WPML, which scopes results through the `posts_*`
 *   query filters that this flag turns off.
 * - `lang => ''` covers Polylang, which never reads `suppress_filters`. It hooks
 *   `parse_query` and mutates `query_vars['tax_query']` directly, and WordPress
 *   re-parses that tax query inside WP_Query::get_posts() on a branch gated on
 *   `! $this->is_singular` — nothing there consults `suppress_filters`, so the
 *   language clause survives it. What Polylang does honour is the `lang` query
 *   var: PLL_Query::is_already_filtered() treats it as "the caller has already
 *   decided", and `isset()` is the whole test, so an empty value is enough.
 *
 * An empty `lang` is inert everywhere else: without Polylang no taxonomy claims
 * that query var, and WP_Query skips taxonomy query vars whose value is empty.
 *
 * @param array<string,mixed> $args get_posts() arguments (without suppress_filters).
 * @return WP_Post|null
 */
function pediment_child_find_nav_post( array $args ) {
	$args['post_type']        = 'wp_navigation';
	$args['numberposts']      = 1;
	$args['suppress_filters'] = false;

	$found = get_posts( $args );
	if ( $found ) {
		return $found[0];
	}

	$args['suppress_filters'] = true;
	$args['lang']             = '';
	$found                    = get_posts( $args );

	return $found ? $found[0] : null;
}

/**
 * Swap a menu resolved in one language for the current language's translation.
 *
 * The lookups below are language-scoped first, so they only return the right
 * menu per language when *every* translation carries the marker. Translations
 * made the only way a site owner can make them -- the "+" buttons on the
 * Navigation Menus screen -- never do: wp_navigation is registered without
 * `custom-fields` support, so no admin screen exists on which post meta could be
 * stamped by hand. The scoped query therefore finds nothing, the unscoped retry
 * in pediment_child_find_nav_post() falls back to the default language's menu,
 * and every translated page silently renders the default language's navigation.
 *
 * Following Polylang's translation group instead is what makes menus created
 * through the admin work with no further steps. The marker is stamped on
 * whatever is adopted, so the scoped query resolves it directly from then on and
 * this path is walked once per menu rather than once per request.
 *
 * The default language is tried second, and that ordering is load-bearing. Once
 * this function has stamped the marker across a translation group, the unscoped
 * retry in pediment_child_find_nav_post() can no longer tell the translations
 * apart -- it takes the newest of several equally-marked menus, so a language
 * whose own menu is missing or unpublished would inherit whichever translation
 * happened to be saved last. Falling back to the default language keeps that
 * deterministic and comprehensible.
 *
 * @param WP_Post         $menu        Menu resolved by marker or slug.
 * @param string|string[] $post_status Status(es) to accept.
 * @return WP_Post Current-language translation, else default-language, else $menu.
 */
function pediment_child_adopt_translated_nav( WP_Post $menu, $post_status ): WP_Post {
	if ( ! function_exists( 'pll_current_language' ) || ! function_exists( 'pll_get_post' ) ) {
		return $menu;
	}

	$statuses = (array) $post_status;
	$default  = function_exists( 'pll_default_language' ) ? pll_default_language() : '';

	foreach ( array( pll_current_language(), $default ) as $lang ) {
		if ( ! $lang ) {
			continue;
		}
		if ( pll_get_post_language( $menu->ID ) === $lang ) {
			return $menu;
		}

		$translated = pll_get_post( $menu->ID, $lang );
		if ( ! $translated ) {
			continue;
		}

		// Status is re-checked rather than trusted: the caller asking only for
		// `publish` must not be handed a draft translation, which would put an
		// unfinished menu in front of visitors.
		$post = get_post( $translated );
		if ( ! $post instanceof WP_Post || ! in_array( $post->post_status, $statuses, true ) ) {
			continue;
		}

		update_post_meta( $post->ID, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );

		return $post;
	}

	return $menu;
}

/**
 * The Primary menu whatever its status, by marker and then by legacy slug.
 *
 * Stamps the marker when it has to fall back to the slug, so sites seeded
 * before the marker existed migrate themselves on first lookup and stop
 * depending on the slug.
 *
 * @param string|string[] $post_status Status(es) to accept.
 * @return WP_Post|null
 */
function pediment_child_find_primary_nav( $post_status ) {
	$menu = pediment_child_find_nav_post(
		array(
			'post_status' => $post_status,
			'meta_key'    => PEDIMENT_CHILD_PRIMARY_NAV_MARKER, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One indexed lookup per request; the consumer early-returns for non-navigation blocks.
		)
	);

	if ( ! $menu ) {
		$menu = pediment_child_find_nav_post(
			array(
				'post_status' => $post_status,
				'name'        => 'primary',
			)
		);
		if ( $menu ) {
			update_post_meta( $menu->ID, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );
		}
	}

	if ( ! $menu ) {
		return null;
	}

	return pediment_child_adopt_translated_nav( $menu, $post_status );
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
			'<!-- wp:navigation-link {"label":"Find your way around","url":"/guide/map/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"FAQ","url":"/guide/faq/","kind":"custom"} /-->',
			'<!-- wp:navigation-link {"label":"More","url":"/guide/","kind":"custom"} /-->',
			'<!-- /wp:navigation-submenu -->',
			'<!-- wp:navigation-link {"label":"Contact","url":"/contact-us/","kind":"custom","isTopLevelLink":true} /-->',

			/*
			 * Polylang's own switcher, kept in the source so every generated
			 * language menu carries it. Polylang requires the block to be added to
			 * each language's menu by hand (its docs say so explicitly), which on a
			 * five-language site means five edits that nothing keeps in sync.
			 *
			 * `dropdown` is not cosmetic: flat mode renders one top-level item per
			 * language, which would put five extra entries in a header that already
			 * has four.
			 *
			 * Do not add `hide_current` to make the list stop repeating the language
			 * named on the toggle. In dropdown mode Polylang renders the *first list
			 * item* as the toggle, so removing the current language promotes an
			 * arbitrary other one: on /de/ the header then reads "English", and
			 * "English" still appears in the list below it. Verified against free
			 * Polylang 3.8.6. The repetition is the lesser evil.
			 *
			 * Free Polylang 3.8 registers this with `parent: core/navigation`, so it
			 * is only valid here. When the plugin is inactive the block is
			 * unregistered and renders as nothing -- guarded by
			 * PrimaryNavRenderTest, which runs in an environment without Polylang.
			 */
			'<!-- wp:polylang/navigation-language-switcher {"dropdown":true} /-->',
		)
	);
}

/**
 * The published "Primary" navigation menu, or null if it hasn't been seeded.
 *
 * @return WP_Post|null
 */
function pediment_child_get_primary_nav_menu() {
	// Only a published menu renders; an unpublished one is the seed's problem to
	// heal, not something to bind the header to.
	return pediment_child_find_primary_nav( 'publish' );
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

/**
 * Give a dropdown parent an active state on its own landing page.
 *
 * The core Navigation block adds `aria-current` to leaf navigation-link items by
 * URL match, but never to navigation-submenu parents — so viewing a dropdown's
 * own page (e.g. /guide/, /ways-to-stay/) would leave no link marked active.
 * Inject `aria-current="page"` on the parent's own anchor when its URL is the
 * current request. Child pages are handled in CSS via `:has()` off the leaf that
 * core already flags, so skip whenever a descendant is already current.
 *
 * @param string $content Rendered block HTML.
 * @param array  $block   Parsed block.
 * @return string
 */
function pediment_child_mark_current_submenu_parent( $content, $block ) {
	if ( 'core/navigation-submenu' !== ( isset( $block['blockName'] ) ? $block['blockName'] : '' ) ) {
		return $content;
	}
	$url = isset( $block['attrs']['url'] ) ? (string) $block['attrs']['url'] : '';
	if ( '' === $url || false !== strpos( $content, 'aria-current' ) ) {
		return $content;
	}
	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$current = (string) wp_parse_url( $request, PHP_URL_PATH );
	$target  = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( '' === $current || '' === $target || untrailingslashit( $current ) !== untrailingslashit( $target ) ) {
		return $content;
	}
	// Insert on the first content anchor only — the parent's own link, which
	// precedes the submenu's child anchors.
	return preg_replace(
		'/<a (?=[^>]*\bwp-block-navigation-item__content\b)/',
		'<a aria-current="page" ',
		$content,
		1
	);
}
add_filter( 'render_block', 'pediment_child_mark_current_submenu_parent', 10, 2 );
