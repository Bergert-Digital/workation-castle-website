<?php
/**
 * Per-language Primary menus.
 *
 * One wp_navigation post per Polylang language, generated from the theme's
 * canonical nav source. Lives apart from inc/PrimaryNav.php, which owns menu
 * lookup and rendering, and apart from inc/seed.php, which would otherwise have
 * to know about Polylang as well as pages, photos and rewrite rules.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu labels, keyed by the English label they replace.
 *
 * Only labels live here. Menu *structure* stays in
 * pediment_child_primary_nav_blocks(), which is the single source of truth for
 * what the menu contains -- five hardcoded markup blobs would drift the moment
 * somebody edited the English menu, and the drift would stay invisible until a
 * client clicked it.
 *
 * NavTranslationsTest asserts that every label in that source appears here, and
 * that every entry covers the same languages, so editing the nav source without
 * translating the new item fails the build rather than shipping.
 */
const PEDIMENT_CHILD_NAV_LABELS = array(
	'Activities'           => array(
		'de' => 'Aktivitäten',
		'nl' => 'Activiteiten',
		'fr' => 'Activités',
		'it' => 'Attività',
	),
	'Photos'               => array(
		'de' => 'Fotos',
		'nl' => 'Fotogalerij',
		'fr' => 'Photographies',
		'it' => 'Fotografie',
	),
	'Ways to stay'         => array(
		'de' => 'Aufenthaltsarten',
		'nl' => 'Manieren van verblijf',
		'fr' => 'Façons de séjourner',
		'it' => 'Modi di soggiornare',
	),
	'Team retreats'        => array(
		'de' => 'Team-Retreats',
		'nl' => 'Teamretraites',
		'fr' => "Séminaires d'équipe",
		'it' => 'Ritiri aziendali',
	),
	'Workations'           => array(
		'de' => 'Workations',
		'nl' => 'Workations',
		'fr' => 'Workations',
		'it' => 'Workation',
	),
	'Family & group stays' => array(
		'de' => 'Familien & Gruppen',
		'nl' => 'Familie & groepen',
		'fr' => 'Familles & groupes',
		'it' => 'Famiglie e gruppi',
	),
	'Guest Guide'          => array(
		'de' => 'Gästeführer',
		'nl' => 'Gastengids',
		'fr' => 'Guide du séjour',
		'it' => "Guida dell'ospite",
	),
	'How to get here'      => array(
		'de' => 'Anreise',
		'nl' => 'Hoe u ons bereikt',
		'fr' => 'Comment venir',
		'it' => 'Come arrivare',
	),
	'Checking in'          => array(
		'de' => 'Anmeldung',
		'nl' => 'Inchecken',
		'fr' => 'Enregistrement',
		'it' => 'Registrazione',
	),
	'Find your way around' => array(
		'de' => 'Orientierung',
		'nl' => 'Vind uw weg',
		'fr' => "S'orienter",
		'it' => 'Orientarsi',
	),
	'FAQ'                  => array(
		'de' => 'FAQ',
		'nl' => 'FAQ',
		'fr' => 'FAQ',
		'it' => 'FAQ',
	),
	'More'                 => array(
		'de' => 'Mehr',
		'nl' => 'Meer',
		'fr' => 'Plus',
		'it' => 'Altro',
	),
	'Contact'              => array(
		'de' => 'Kontakt',
		'nl' => 'Contact opnemen',
		'fr' => 'Contactez-nous',
		'it' => 'Contatti',
	),
);

/**
 * Map a canonical English menu URL to the same page's permalink in one language.
 *
 * Resolution is by *full path*, not by trailing slug: `faq` exists under
 * `guide/` in English and under `gastefuhrer/` in German, and WordPress scopes
 * slug uniqueness for hierarchical types by parent, so a bare slug lookup cannot
 * disambiguate them.
 *
 * @param string $url  Canonical URL from the nav source, e.g. `/guide/faq/`.
 * @param string $lang Target language slug.
 * @return string|null Relative translated URL, or null when it cannot be mapped.
 */
function pediment_child_translate_nav_url( string $url, string $lang ) {
	if ( ! function_exists( 'pll_get_post' ) ) {
		return null;
	}

	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return null;
	}

	// Core appends `attachment` to a string post type, so the match is confirmed
	// to be a page rather than an attachment sharing the path.
	$page = get_page_by_path( $path );
	if ( ! $page instanceof WP_Post || 'page' !== $page->post_type ) {
		return null;
	}

	$translated = pll_get_post( $page->ID, $lang );
	if ( ! $translated ) {
		return null;
	}

	// Status is re-checked rather than trusted: pll_get_post() returns a
	// translation whatever its status, and a draft or trashed one must not become
	// a live menu link to unpublished content. Declining here leaves the caller
	// with the English URL and its existing "cannot map" warning.
	$post = get_post( $translated );
	if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
		return null;
	}

	return wp_make_link_relative( (string) get_permalink( $post ) );
}

/**
 * Translate labels and URLs through a parsed navigation block tree.
 *
 * Operates on parsed blocks rather than by string surgery, so nested
 * navigation-submenu items are handled by the same code as top-level links and
 * innerContent stays consistent for serialize_blocks().
 *
 * A label with no entry, or a URL with no translated target, is left as it is
 * and logged. Leaving the English value is deliberate: a menu that is half
 * translated still navigates, whereas a dropped item does not.
 *
 * @param array[] $blocks Parsed blocks.
 * @param string  $lang   Target language slug.
 * @param array   $log    Log lines, appended to by reference.
 * @return array[] Translated parsed blocks.
 */
function pediment_child_translate_nav_blocks( array $blocks, string $lang, array &$log ): array {
	foreach ( $blocks as &$block ) {
		if ( isset( $block['attrs']['label'] ) ) {
			$label = $block['attrs']['label'];
			if ( isset( PEDIMENT_CHILD_NAV_LABELS[ $label ][ $lang ] ) ) {
				$block['attrs']['label'] = PEDIMENT_CHILD_NAV_LABELS[ $label ][ $lang ];
			} else {
				$log[] = sprintf( 'nav translations: WARNING no %s label for menu item "%s"', $lang, $label );
			}
		}

		if ( isset( $block['attrs']['url'] ) ) {
			$translated = pediment_child_translate_nav_url( $block['attrs']['url'], $lang );
			if ( null === $translated ) {
				$log[] = sprintf( 'nav translations: WARNING cannot map %s url "%s"', $lang, $block['attrs']['url'] );
			} else {
				$block['attrs']['url'] = $translated;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pediment_child_translate_nav_blocks( $block['innerBlocks'], $lang, $log );
		}
	}
	unset( $block );

	return $blocks;
}

/**
 * The marked Primary menu in Polylang's default language, resolved explicitly
 * rather than through the current-language-following lookup.
 *
 * The helper pediment_child_find_primary_nav() (inc/PrimaryNav.php) follows
 * pll_current_language() to hand back whatever menu the *current* language
 * resolves to. Under WP-CLI that language is unset; in wp-admin — exactly
 * where the Tools -> Seed content button runs — it is whatever the admin-bar
 * language filter happens to be set to. Both callers below need the
 * *default*-language menu specifically, regardless of what is currently
 * selected: pediment_child_seed_nav_translations() to know which menu is the
 * translation source, and Seed::seed_primary_nav() to know which menu it
 * owns and is allowed to create or fill. Handing either of them a menu
 * resolved by current language risks operating on the wrong language's menu
 * entirely — see the regression this guards against in the seed step's log
 * line below.
 *
 * @param string|string[] $post_status Status(es) to accept.
 * @return WP_Post|null Null when Polylang is inactive/unconfigured, or no
 *                       such menu exists yet.
 */
function pediment_child_find_default_language_nav( $post_status ) {
	if ( ! function_exists( 'pll_default_language' ) ) {
		return null;
	}

	$default = (string) pll_default_language();
	if ( '' === $default ) {
		return null;
	}

	$found = get_posts(
		array(
			'post_type'        => 'wp_navigation',
			'post_status'      => $post_status,
			'numberposts'      => 1,
			'lang'             => $default,
			'meta_key'         => PEDIMENT_CHILD_PRIMARY_NAV_MARKER, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Runs once per seed, not per request.
			'suppress_filters' => false,
		)
	);

	return $found ? $found[0] : null;
}

/**
 * Create or refresh one Primary menu per non-default language.
 *
 * Called by Seed::seed(), so the Tools -> Seed content button and the WP-CLI
 * command both produce translated menus. Idempotent: every run rebuilds each
 * translated menu's content from the nav source, which is also what upgrades its
 * URLs as real page translations appear.
 *
 * Translated menus are generated artifacts, not curated content. An item added
 * to one in the Site Editor is replaced on the next run -- a nav item that should
 * exist in German belongs in pediment_child_primary_nav_blocks(), where every
 * language gets it and the change is in version control. The English menu is not
 * touched here; seed_primary_nav() owns it and never overwrites its content.
 *
 * @return string[] Log lines.
 */
function pediment_child_seed_nav_translations(): array {
	if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_get_post' ) ) {
		return array( 'nav translations: Polylang inactive — skipped' );
	}

	$languages = (array) pll_languages_list();
	$default   = (string) pll_default_language();
	if ( count( $languages ) < 2 || '' === $default ) {
		return array( 'nav translations: fewer than two languages configured — skipped' );
	}

	// See pediment_child_find_default_language_nav() for why this cannot be
	// pediment_child_get_primary_nav_menu() or pediment_child_find_primary_nav():
	// both follow the current language, and everything below must treat the
	// *default*-language menu as the translation group's source.
	$source = pediment_child_find_default_language_nav( array( 'publish', 'draft', 'pending', 'private', 'future' ) );
	if ( ! $source ) {
		return array( 'nav translations: no Primary menu in the default language — has the seed run?' );
	}

	$log = array();

	foreach ( $languages as $lang ) {
		if ( $lang === $default ) {
			continue;
		}

		$content = serialize_blocks(
			pediment_child_translate_nav_blocks(
				parse_blocks( pediment_child_primary_nav_blocks() ),
				$lang,
				$log
			)
		);

		// pll_get_post() returns whatever ID the translation group holds even when
		// the post behind it is gone (e.g. deleted straight from the Site Editor
		// without unlinking the translation). Requiring the post to still exist
		// keeps a stale group entry from permanently wedging this language:
		// without the check, wp_update_post() below would fail with
		// WP_Error( 'invalid_post' ) on every future run and this step would
		// never fall through to create a replacement.
		$existing = pll_get_post( $source->ID, $lang );
		if ( $existing && ! get_post( $existing ) ) {
			$existing = 0;
		}

		if ( $existing ) {
			// Keep the ID: it preserves the translation group and anything already
			// pointing at this menu. Re-assert publish, which heals a menu someone
			// unpublished in the Site Editor -- that otherwise leaves the language
			// with no navigation while this step reports success.
			$updated = wp_update_post(
				array(
					'ID'           => (int) $existing,
					'post_status'  => 'publish',
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( is_wp_error( $updated ) || ! $updated ) {
				$log[] = sprintf(
					'nav translations: FAILED updating %s menu (ID %d)%s',
					$lang,
					(int) $existing,
					is_wp_error( $updated ) ? ' — ' . $updated->get_error_message() : ''
				);
				continue;
			}
			$menu_id = (int) $existing;
			$log[]   = sprintf( 'nav translations: updated %s menu (ID %d)', $lang, $menu_id );
		} else {
			$menu_id = wp_insert_post(
				array(
					'post_type'    => 'wp_navigation',
					'post_status'  => 'publish',
					'post_title'   => 'Primary (' . strtoupper( $lang ) . ')',
					'post_name'    => 'primary-' . $lang,
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( is_wp_error( $menu_id ) ) {
				$log[] = sprintf( 'nav translations: FAILED creating %s menu — %s', $lang, $menu_id->get_error_message() );
				continue;
			}

			pll_set_post_language( $menu_id, $lang );

			/*
			 * pll_save_post_translations() replaces the whole group, so the existing
			 * members have to be passed back in. Handing it a bare pair silently
			 * unlinks every language saved before it -- invisible with one
			 * translation, wrong with four.
			 */
			$translations             = pll_get_post_translations( $source->ID );
			$translations[ $default ] = $source->ID;
			$translations[ $lang ]    = $menu_id;
			pll_save_post_translations( $translations );

			$log[] = sprintf( 'nav translations: created %s menu (ID %d)', $lang, $menu_id );
		}

		// The header resolves menus by this marker. Without it the language-scoped
		// lookup misses and the language falls back to the default menu.
		update_post_meta( $menu_id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );
	}

	return $log;
}
