<?php
/**
 * Dev-only Polylang bootstrap, executed inside WordPress by setup-polylang.mjs:
 *
 *   wp eval-file wp-content/themes/<slug>/tools/polylang-setup.php
 *
 * Polylang's free build ships no WP-CLI commands, so everything here goes
 * through the PLL() API. `tools/` is listed in .distignore, so this file never
 * reaches a release zip.
 *
 * Idempotent: every step checks before it writes, so it is safe to run on each
 * `npm run env:setup`. That matters because the content seed runs first and
 * creates pages, the navigation menu and CPT posts with no language attached —
 * and untagged content is what silently cost the header its navigation on a
 * real site.
 *
 * @package PedimentChild
 */

if ( ! function_exists( 'PLL' ) || ! PLL() ) {
	echo "polylang: plugin not active — nothing to do\n";
	return;
}

/** Default first: Polylang treats the lowest term_group as the default language. */
const PEDIMENT_CHILD_DEV_LANGUAGES = array(
	array(
		'slug'   => 'en',
		'name'   => 'English',
		'locale' => 'en_US',
		'flag'   => 'gb',
	),
	array(
		'slug'   => 'de',
		'name'   => 'Deutsch',
		'locale' => 'de_DE',
		'flag'   => 'de',
	),
	array(
		'slug'   => 'nl',
		'name'   => 'Nederlands',
		'locale' => 'nl_NL',
		'flag'   => 'nl',
	),
	array(
		'slug'   => 'fr',
		'name'   => 'Français',
		'locale' => 'fr_FR',
		'flag'   => 'fr',
	),
	array(
		'slug'   => 'it',
		'name'   => 'Italiano',
		'locale' => 'it_IT',
		'flag'   => 'it',
	),
);

/** English is the default so its URLs stay unprefixed, which the e2e suite relies on. */
const PEDIMENT_CHILD_DEV_DEFAULT_LANG = 'en';

$model = PLL()->model;

// -----------------------------------------------------------------------------
// 1. Languages
// -----------------------------------------------------------------------------
$existing = wp_list_pluck( $model->get_languages_list(), 'slug' );

foreach ( PEDIMENT_CHILD_DEV_LANGUAGES as $index => $language ) {
	if ( in_array( $language['slug'], $existing, true ) ) {
		continue;
	}
	$language['rtl']        = 0;
	$language['term_group'] = $index;

	$added = $model->add_language( $language );
	printf(
		"polylang: language %s %s\n",
		$language['slug'],
		is_wp_error( $added ) ? 'FAILED — ' . $added->get_error_message() : 'created'
	);
}

$model->clean_languages_cache();

// -----------------------------------------------------------------------------
// 2. Options: default language + what is translatable
// -----------------------------------------------------------------------------
$options = get_option( 'polylang', array() );

// wp_navigation has to be translatable for a menu to exist per language. The
// header resolves its menu with a language-scoped query, so each language binds
// its own; see inc/PrimaryNav.php.
$translatable = array( 'wp_navigation' );
foreach ( array( 'PEDIMENT_CHILD_ACTIVITY_CPT', 'PEDIMENT_CHILD_PHOTO_CPT' ) as $constant ) {
	if ( defined( $constant ) ) {
		$translatable[] = constant( $constant );
	}
}

$options['default_lang'] = PEDIMENT_CHILD_DEV_DEFAULT_LANG;
$options['post_types']   = array_values( array_unique( array_merge( isset( $options['post_types'] ) ? (array) $options['post_types'] : array(), $translatable ) ) );
update_option( 'polylang', $options );

printf( "polylang: default language %s, translatable: %s\n", PEDIMENT_CHILD_DEV_DEFAULT_LANG, implode( ', ', $translatable ) );

// -----------------------------------------------------------------------------
// 3. Tag everything the seed left untagged
// -----------------------------------------------------------------------------
$tagged     = 0;
$post_types = array_merge( array( 'page', 'post', 'wp_navigation' ), $translatable );

foreach ( array_unique( $post_types ) as $post_type ) {
	$ids = get_posts(
		array(
			'post_type'        => $post_type,
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);
	foreach ( $ids as $id ) {
		if ( pll_get_post_language( $id ) ) {
			continue;
		}
		pll_set_post_language( $id, PEDIMENT_CHILD_DEV_DEFAULT_LANG );
		++$tagged;
	}
}

printf( "polylang: tagged %d untagged object(s) as %s\n", $tagged, PEDIMENT_CHILD_DEV_DEFAULT_LANG );

// -----------------------------------------------------------------------------
// 4. German stub: translations of Home and Contact, plus a German menu
// -----------------------------------------------------------------------------

/**
 * Find or create the German translation of an English page.
 *
 * @param string $slug        English page slug.
 * @param string $german_slug Slug for the German page.
 * @param string $title       German page title.
 * @return int German page ID, or 0 when the English page is missing.
 */
function pediment_child_dev_translate_page( string $slug, string $german_slug, string $title ): int {
	$source = get_page_by_path( $slug );
	if ( ! $source ) {
		return 0;
	}

	$existing = pll_get_post( $source->ID, 'de' );
	if ( $existing ) {
		return (int) $existing;
	}

	// Same body as the English page: this is a navigable stub for checking that
	// /de/ resolves, not a translation. Translating patterns/ is editorial work
	// and would go stale against the seed.
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $german_slug,
			'post_content' => $source->post_content,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		printf( "polylang: FAILED creating %s — %s\n", $german_slug, $id->get_error_message() );
		return 0;
	}

	pll_set_post_language( $id, 'de' );
	pll_save_post_translations(
		array(
			'en' => $source->ID,
			'de' => $id,
		)
	);
	printf( "polylang: created German page %s (ID %d)\n", $german_slug, $id );

	return (int) $id;
}

$de_home    = pediment_child_dev_translate_page( 'home', 'startseite', 'Startseite' );
$de_contact = pediment_child_dev_translate_page( 'contact-us', 'kontakt', 'Kontakt' );

// German Primary menu. It links only to the two pages that exist in German —
// pointing at the untranslated rest would just manufacture 404s.
$existing_de_menu = get_posts(
	array(
		'post_type'        => 'wp_navigation',
		'post_status'      => 'any',
		'numberposts'      => 1,
		'name'             => 'primary-de',
		'suppress_filters' => true,
	)
);

if ( $existing_de_menu ) {
	printf( "polylang: German menu already present (ID %d)\n", $existing_de_menu[0]->ID );
} elseif ( $de_home && $de_contact ) {
	$items = implode(
		"\n",
		array(
			'<!-- wp:navigation-link {"label":"Startseite","url":"' . wp_make_link_relative( (string) get_permalink( $de_home ) ) . '","kind":"custom","isTopLevelLink":true} /-->',
			'<!-- wp:navigation-link {"label":"Kontakt","url":"' . wp_make_link_relative( (string) get_permalink( $de_contact ) ) . '","kind":"custom","isTopLevelLink":true} /-->',
		)
	);

	$menu_id = wp_insert_post(
		array(
			'post_type'    => 'wp_navigation',
			'post_status'  => 'publish',
			'post_title'   => 'Primary (Deutsch)',
			'post_name'    => 'primary-de',
			'post_content' => $items,
		),
		true
	);

	if ( is_wp_error( $menu_id ) ) {
		printf( "polylang: FAILED creating German menu — %s\n", $menu_id->get_error_message() );
	} else {
		pll_set_post_language( $menu_id, 'de' );
		// Same marker as the English menu, so the header's language-scoped lookup
		// binds whichever menu matches the request's language.
		if ( defined( 'PEDIMENT_CHILD_PRIMARY_NAV_MARKER' ) ) {
			update_post_meta( $menu_id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );
		}
		$english_menu = pediment_child_get_primary_nav_menu();
		if ( $english_menu ) {
			pll_save_post_translations(
				array(
					'en' => $english_menu->ID,
					'de' => $menu_id,
				)
			);
		}
		printf( "polylang: created German menu (ID %d)\n", $menu_id );
	}
}

flush_rewrite_rules( false );
echo "polylang: ready\n";
