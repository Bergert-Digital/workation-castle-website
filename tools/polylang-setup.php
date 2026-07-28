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
// 4. Stub pages in every non-default language
// -----------------------------------------------------------------------------

/**
 * Every seeded page, parent-first, with a title per language.
 *
 * Parent-first order is load-bearing: a child's post_parent must point at the
 * *translated* parent, or its permalink comes out flat and every menu URL in that
 * language breaks. The loop below relies on a parent already existing when its
 * children are reached.
 *
 * A declared list rather than a scan of the database, because the dev database
 * collects strays -- an empty `home-english` page, for one -- and a scan would
 * multiply every stray by four.
 *
 * Titles are deliberately distinct across languages. Polylang does not hook
 * `wp_unique_post_slug`, so all top-level pages share one slug namespace whatever
 * their language: two titles that sanitize alike would land as `kontakt-2`.
 */
const PEDIMENT_CHILD_DEV_PAGES = array(
	// Top level.
	'home'            => array( 'de' => 'Startseite', 'nl' => 'Startpagina', 'fr' => 'Accueil', 'it' => 'Pagina iniziale' ),
	'activities'      => array( 'de' => 'Aktivitäten', 'nl' => 'Activiteiten', 'fr' => 'Activités', 'it' => 'Attività' ),
	'photos'          => array( 'de' => 'Fotos', 'nl' => 'Fotogalerij', 'fr' => 'Photographies', 'it' => 'Fotografie' ),
	'reviews'         => array( 'de' => 'Bewertungen', 'nl' => 'Beoordelingen', 'fr' => 'Avis', 'it' => 'Recensioni' ),
	'ways-to-stay'    => array( 'de' => 'Aufenthaltsarten', 'nl' => 'Manieren van verblijf', 'fr' => 'Façons de séjourner', 'it' => 'Modi di soggiornare' ),
	'guide'           => array( 'de' => 'Gästeführer', 'nl' => 'Gastengids', 'fr' => 'Guide du séjour', 'it' => 'Guida per gli ospiti' ),
	'check-in'        => array( 'de' => 'Anmeldung', 'nl' => 'Inchecken', 'fr' => 'Enregistrement', 'it' => 'Registrazione' ),
	'contact-us'      => array( 'de' => 'Kontakt', 'nl' => 'Contact opnemen', 'fr' => 'Contactez-nous', 'it' => 'Contatti' ),
	'feedback'        => array( 'de' => 'Rückmeldung', 'nl' => 'Terugkoppeling', 'fr' => 'Commentaires', 'it' => 'Commenti' ),
	'imprint'         => array( 'de' => 'Impressum', 'nl' => 'Colofon', 'fr' => 'Mentions légales', 'it' => 'Note legali' ),
	'privacy-policy'  => array( 'de' => 'Datenschutzerklärung', 'nl' => 'Privacybeleid', 'fr' => 'Politique de confidentialité', 'it' => 'Informativa sulla privacy' ),
	// Children of `guide`.
	'arrival'         => array( 'de' => 'Anreise', 'nl' => 'Aankomst', 'fr' => 'Arrivée', 'it' => 'Arrivo' ),
	'casa-galbiga'    => array( 'de' => 'Casa Galbiga', 'nl' => 'Casa Galbiga', 'fr' => 'Casa Galbiga', 'it' => 'Casa Galbiga' ),
	'faq'             => array( 'de' => 'FAQ', 'nl' => 'FAQ', 'fr' => 'FAQ', 'it' => 'FAQ' ),
	'map'             => array( 'de' => 'Karte', 'nl' => 'Kaart', 'fr' => 'Plan', 'it' => 'Mappa' ),
	// Children of `ways-to-stay`.
	'team-retreats'   => array( 'de' => 'Team-Retreats', 'nl' => 'Teamretraites', 'fr' => "Séminaires d'équipe", 'it' => 'Ritiri aziendali' ),
	'workations'      => array( 'de' => 'Workations', 'nl' => 'Workations', 'fr' => 'Workations', 'it' => 'Workation' ),
	'family-and-groups' => array( 'de' => 'Familien & Gruppen', 'nl' => 'Familie & groepen', 'fr' => 'Familles & groupes', 'it' => 'Famiglie e gruppi' ),
);

/**
 * Every English page, keyed by slug.
 *
 * Resolved once and passed around, because a bare `name` query cannot
 * disambiguate: `workations` exists as a slug under a different parent in every
 * language once this script has run, and WordPress scopes slug uniqueness for
 * hierarchical types by parent.
 *
 * @return array<string, WP_Post>
 */
function pediment_child_dev_english_pages(): array {
	$pages = array();
	$found = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => -1,
			'lang'        => PEDIMENT_CHILD_DEV_DEFAULT_LANG,
		)
	);
	foreach ( $found as $page ) {
		$pages[ $page->post_name ] = $page;
	}
	return $pages;
}

/**
 * Add one language to a post's translation group without dropping the others.
 *
 * pll_save_post_translations() replaces the whole group. Handing it a bare pair
 * (`en` + the language being processed) silently unlinks every language saved
 * before it -- invisible with one translation, fatal with four.
 *
 * @param int    $source_id     English post ID.
 * @param string $lang          Language slug being added.
 * @param int    $translated_id Post ID in that language.
 */
function pediment_child_dev_link_translation( int $source_id, string $lang, int $translated_id ): void {
	$translations          = pll_get_post_translations( $source_id );
	$translations['en']    = $source_id;
	$translations[ $lang ] = $translated_id;
	pll_save_post_translations( $translations );
}

/**
 * Find or create one page's translation.
 *
 * The body is copied from the English source verbatim. This is a navigable stub
 * for exercising the multilingual plumbing, not a translation: translating the
 * block patterns is editorial work, and it would go stale against the seed, which
 * regenerates English from files while translations sit frozen in the database.
 *
 * @param WP_Post $source English page.
 * @param string  $lang   Target language slug.
 * @param string  $title  Translated title.
 * @return int Translated page ID, or 0 on failure.
 */
function pediment_child_dev_translate_page( WP_Post $source, string $lang, string $title ): int {
	$existing = pll_get_post( $source->ID, $lang );
	if ( $existing ) {
		return (int) $existing;
	}

	$parent = 0;
	if ( $source->post_parent ) {
		$parent = (int) pll_get_post( $source->post_parent, $lang );
		if ( ! $parent ) {
			printf(
				"polylang: FAILED %s (%s) — translated parent missing; is PEDIMENT_CHILD_DEV_PAGES parent-first?\n",
				$source->post_name,
				$lang
			);
			return 0;
		}
	}

	$slug = sanitize_title( $title );
	$id   = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_parent'  => $parent,
			'post_content' => $source->post_content,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		printf( "polylang: FAILED creating %s (%s) — %s\n", $slug, $lang, $id->get_error_message() );
		return 0;
	}

	// Polylang does not hook wp_unique_post_slug, so a clash across languages is
	// resolved by WordPress appending -2. Surface it: it means two titles in
	// PEDIMENT_CHILD_DEV_PAGES sanitize alike, which is a data bug to fix there.
	$actual = get_post_field( 'post_name', $id );
	if ( $actual !== $slug ) {
		printf(
			"polylang: WARNING %s (%s) wanted slug '%s' but got '%s' — choose a distinct title\n",
			$source->post_name,
			$lang,
			$slug,
			$actual
		);
	}

	pll_set_post_language( $id, $lang );
	pediment_child_dev_link_translation( (int) $source->ID, $lang, (int) $id );

	return (int) $id;
}

$english_pages = pediment_child_dev_english_pages();
$created       = 0;
$missing       = array();

foreach ( PEDIMENT_CHILD_DEV_LANGUAGES as $language ) {
	$lang = $language['slug'];
	if ( PEDIMENT_CHILD_DEV_DEFAULT_LANG === $lang ) {
		continue;
	}

	foreach ( PEDIMENT_CHILD_DEV_PAGES as $english_slug => $titles ) {
		if ( ! isset( $english_pages[ $english_slug ] ) ) {
			$missing[ $english_slug ] = true;
			continue;
		}
		if ( ! isset( $titles[ $lang ] ) ) {
			continue;
		}
		$source = $english_pages[ $english_slug ];
		if ( pll_get_post( $source->ID, $lang ) ) {
			continue;
		}
		if ( pediment_child_dev_translate_page( $source, $lang, $titles[ $lang ] ) ) {
			++$created;
		}
	}
}

printf( "polylang: created %d stub page(s)\n", $created );
if ( $missing ) {
	printf(
		"polylang: no English source for %s — has the content seed run?\n",
		implode( ', ', array_keys( $missing ) )
	);
}

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
