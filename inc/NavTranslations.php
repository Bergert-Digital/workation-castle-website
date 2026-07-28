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
