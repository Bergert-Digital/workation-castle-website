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
// 2. Options: default language, language roots, what is translatable
// -----------------------------------------------------------------------------
$options = PLL()->options;

// wp_navigation has to be translatable for a menu to exist per language. The
// header resolves its menu with a language-scoped query, so each language binds
// its own; see inc/PrimaryNav.php.
$translatable = array( 'wp_navigation' );
foreach ( array( 'PEDIMENT_CHILD_ACTIVITY_CPT', 'PEDIMENT_CHILD_PHOTO_CPT' ) as $constant ) {
	if ( defined( $constant ) ) {
		$translatable[] = constant( $constant );
	}
}

$desired = array(
	'default_lang'  => PEDIMENT_CHILD_DEV_DEFAULT_LANG,
	'post_types'    => array_values( array_unique( array_merge( (array) $options['post_types'], $translatable ) ) ),

	/*
	 * Serve each language at its own root: /de/, not /de/startseite/.
	 *
	 * Polylang defaults `redirect_lang` to 0, which makes a language's home URL
	 * the permalink of its *translated front page* -- see
	 * set_language_home_url() in Polylang's links-model.php. Every /de/ request
	 * then canonically 301s to /de/startseite/, and the language switcher,
	 * hreflang tags and menu home links all point at the page URL. Turning it on
	 * inverts the pair: /de/ serves the front page, /de/startseite/ redirects
	 * there.
	 *
	 * This is the checkbox "The front page url contains the language code
	 * instead of the page name or page id" under Languages -> Settings -> URL
	 * modifications, so a live site needs it ticked there too.
	 */
	'redirect_lang' => 1,
);

/*
 * Written through Polylang's own options object, never with update_option().
 *
 * Since 3.7 Polylang holds its options in memory and flushes them to the
 * database on `shutdown`. A raw update_option() therefore loses twice: nothing
 * else in this process sees the new value -- including the language home URLs
 * cached further down -- and Polylang's shutdown save writes its stale copy back
 * over the row. That is what left `post_types` empty and every language root
 * still redirecting even though the option had visibly been written.
 *
 * merge() applies the keys in Polylang's own registration order, which matters
 * because some of its options validate against others.
 */
$saved = $options->merge( $desired );
if ( is_wp_error( $saved ) && $saved->has_errors() ) {
	printf( "polylang: WARNING option write rejected — %s\n", implode( '; ', $saved->get_error_messages() ) );
}
$options->save();

// Every language object caches the home URL derived from the options above, and
// that cache outlives the write. Without dropping it a re-run against an
// existing database saves the new setting and still serves the old URLs, which
// reads as "the fix did not work".
$model->clean_languages_cache();

printf(
	"polylang: default language %s, language roots serve the front page, translatable: %s\n",
	PEDIMENT_CHILD_DEV_DEFAULT_LANG,
	implode( ', ', $translatable )
);

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
 * The source's own language is read back from Polylang rather than assumed to be
 * `en`: filing a post under the wrong key makes validate_translations() drop it,
 * and save_translations() then unlinks whichever post really held that key.
 *
 * @param int    $source_id     Source post ID (normally the English one).
 * @param string $lang          Language slug being added.
 * @param int    $translated_id Post ID in that language.
 */
function pediment_child_dev_link_translation( int $source_id, string $lang, int $translated_id ): void {
	$source_lang = pll_get_post_language( $source_id );
	if ( ! $source_lang ) {
		printf( "polylang: FAILED linking %s translation — source post %d has no language\n", $lang, $source_id );
		return;
	}

	$translations                 = pll_get_post_translations( $source_id );
	$translations[ $source_lang ] = $source_id;
	$translations[ $lang ]        = $translated_id;
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
			// Slashed: wp_insert_post() unslashes what it is given, so raw block
			// markup would lose every backslash it carries. See the note on the
			// menu bodies below for what that costs.
			'post_content' => wp_slash( $source->post_content ),
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

// -----------------------------------------------------------------------------
// 5. A Primary menu per language
// -----------------------------------------------------------------------------

/**
 * Menu labels, keyed by the English label they replace.
 *
 * Only labels live here. Menu *structure* is derived from
 * pediment_child_primary_nav_blocks(), which the theme already treats as the
 * single source of truth for the menu -- four hardcoded markup blobs would drift
 * the moment somebody edited the English menu, and the drift would stay invisible
 * until a client clicked it.
 */
const PEDIMENT_CHILD_DEV_NAV_LABELS = array(
	'Activities'           => array( 'de' => 'Aktivitäten', 'nl' => 'Activiteiten', 'fr' => 'Activités', 'it' => 'Attività' ),
	'Photos'               => array( 'de' => 'Fotos', 'nl' => 'Fotogalerij', 'fr' => 'Photographies', 'it' => 'Fotografie' ),
	'Ways to stay'         => array( 'de' => 'Aufenthaltsarten', 'nl' => 'Manieren van verblijf', 'fr' => 'Façons de séjourner', 'it' => 'Modi di soggiornare' ),
	'Team retreats'        => array( 'de' => 'Team-Retreats', 'nl' => 'Teamretraites', 'fr' => "Séminaires d'équipe", 'it' => 'Ritiri aziendali' ),
	'Workations'           => array( 'de' => 'Workations', 'nl' => 'Workations', 'fr' => 'Workations', 'it' => 'Workation' ),
	'Family & group stays' => array( 'de' => 'Familien & Gruppen', 'nl' => 'Familie & groepen', 'fr' => 'Familles & groupes', 'it' => 'Famiglie e gruppi' ),
	'Guest Guide'          => array( 'de' => 'Gästeführer', 'nl' => 'Gastengids', 'fr' => 'Guide du séjour', 'it' => "Guida dell'ospite" ),
	'How to get here'      => array( 'de' => 'Anreise', 'nl' => 'Hoe u ons bereikt', 'fr' => 'Comment venir', 'it' => 'Come arrivare' ),
	'Checking in'          => array( 'de' => 'Anmeldung', 'nl' => 'Inchecken', 'fr' => 'Enregistrement', 'it' => 'Registrazione' ),
	'Find your way around' => array( 'de' => 'Orientierung', 'nl' => 'Vind uw weg', 'fr' => "S'orienter", 'it' => 'Orientarsi' ),
	'FAQ'                  => array( 'de' => 'FAQ', 'nl' => 'FAQ', 'fr' => 'FAQ', 'it' => 'FAQ' ),
	'More'                 => array( 'de' => 'Mehr', 'nl' => 'Meer', 'fr' => 'Plus', 'it' => 'Altro' ),
	'Contact'              => array( 'de' => 'Kontakt', 'nl' => 'Contact opnemen', 'fr' => 'Contactez-nous', 'it' => 'Contatti' ),
);

/**
 * Map an English menu URL to the same page's permalink in another language.
 *
 * @param string                $url           English URL, e.g. `/guide/faq/`.
 * @param string                $lang          Target language slug.
 * @param array<string,WP_Post> $english_pages Slug => English page.
 * @return string|null Relative translated URL, or null when it cannot be mapped.
 */
function pediment_child_dev_translate_nav_url( string $url, string $lang, array $english_pages ) {
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( '' === $path ) {
		return null;
	}

	$segments = explode( '/', $path );
	$slug     = (string) end( $segments );
	if ( ! isset( $english_pages[ $slug ] ) ) {
		return null;
	}

	$translated = pll_get_post( $english_pages[ $slug ]->ID, $lang );
	if ( ! $translated ) {
		return null;
	}

	return wp_make_link_relative( (string) get_permalink( $translated ) );
}

/**
 * Translate labels and URLs through a parsed navigation block tree.
 *
 * Operates on parsed blocks rather than by string surgery so nested
 * navigation-submenu items are handled by the same code as top-level links, and
 * so innerContent stays consistent for serialize_blocks().
 *
 * @param array[]               $blocks        Parsed blocks.
 * @param string                $lang          Target language slug.
 * @param array<string,WP_Post> $english_pages Slug => English page.
 * @return array[] Translated parsed blocks.
 */
function pediment_child_dev_translate_nav_blocks( array $blocks, string $lang, array $english_pages ): array {
	foreach ( $blocks as &$block ) {
		if ( isset( $block['attrs']['label'] ) ) {
			$label = $block['attrs']['label'];
			if ( isset( PEDIMENT_CHILD_DEV_NAV_LABELS[ $label ][ $lang ] ) ) {
				$block['attrs']['label'] = PEDIMENT_CHILD_DEV_NAV_LABELS[ $label ][ $lang ];
			} else {
				printf( "polylang: WARNING no %s label for menu item '%s'\n", $lang, $label );
			}
		}

		if ( isset( $block['attrs']['url'] ) ) {
			$translated = pediment_child_dev_translate_nav_url( $block['attrs']['url'], $lang, $english_pages );
			if ( null === $translated ) {
				printf( "polylang: WARNING cannot map %s url '%s'\n", $lang, $block['attrs']['url'] );
			} else {
				$block['attrs']['url'] = $translated;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$block['innerBlocks'] = pediment_child_dev_translate_nav_blocks( $block['innerBlocks'], $lang, $english_pages );
		}
	}
	unset( $block );

	return $blocks;
}

/*
 * Resolve the English menu explicitly rather than through
 * pediment_child_get_primary_nav_menu(). Under WP-CLI Polylang has no current
 * language, so nothing scopes that lookup and it returns the newest menu by
 * date -- which, once this script has run once, is the Italian one. Everything
 * below treats the result as the translation group's English source, so getting
 * it wrong quietly rewires the group.
 */
$english_menus = get_posts(
	array(
		'post_type'   => 'wp_navigation',
		'post_status' => 'publish',
		'numberposts' => 1,
		'lang'        => PEDIMENT_CHILD_DEV_DEFAULT_LANG,
		'meta_key'    => PEDIMENT_CHILD_PRIMARY_NAV_MARKER,
	)
);
$english_menu  = $english_menus ? $english_menus[0] : null;

if ( ! $english_menu ) {
	echo "polylang: no English Primary menu — has the content seed run?\n";
} else {
	foreach ( PEDIMENT_CHILD_DEV_LANGUAGES as $language ) {
		$lang = $language['slug'];
		if ( PEDIMENT_CHILD_DEV_DEFAULT_LANG === $lang ) {
			continue;
		}

		// serialize_block_attributes() encodes with JSON_HEX_AMP but
		// JSON_UNESCAPED_UNICODE, so an ampersand becomes the escape sequence
		// backslash-u0026 while `ä` stays literal. Both wp_insert_post() and
		// wp_update_post() unslash what they are
		// given, so this has to be re-slashed at the call site or the backslash is
		// eaten and "Familie & groepen" renders as "Familie u0026 groepen". Accented
		// labels hid the bug: having no backslash, they survived untouched.
		$content = serialize_blocks(
			pediment_child_dev_translate_nav_blocks(
				parse_blocks( pediment_child_primary_nav_blocks() ),
				$lang,
				$english_pages
			)
		);

		$existing = pll_get_post( $english_menu->ID, $lang );

		if ( $existing ) {
			// Rewrite the body, and re-assert publish. Keeping the ID preserves
			// the translation group and anything already pointing at this menu;
			// re-asserting the status heals a menu a developer unpublished or
			// trashed in the Site Editor, which otherwise leaves that language's
			// header with no navigation while this script reports success.
			$updated = wp_update_post(
				array(
					'ID'           => (int) $existing,
					'post_status'  => 'publish',
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( is_wp_error( $updated ) ) {
				printf( "polylang: FAILED updating %s menu — %s\n", $lang, $updated->get_error_message() );
				continue;
			}
			if ( ! $updated ) {
				printf( "polylang: FAILED updating %s menu (ID %d)\n", $lang, (int) $existing );
				continue;
			}
			$menu_id = (int) $existing;
			printf( "polylang: updated %s menu (ID %d)\n", $lang, $menu_id );
		} else {
			$menu_id = wp_insert_post(
				array(
					'post_type'    => 'wp_navigation',
					'post_status'  => 'publish',
					'post_title'   => 'Primary (' . $language['name'] . ')',
					'post_name'    => 'primary-' . $lang,
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( is_wp_error( $menu_id ) ) {
				printf( "polylang: FAILED creating %s menu — %s\n", $lang, $menu_id->get_error_message() );
				continue;
			}
			pll_set_post_language( $menu_id, $lang );
			pediment_child_dev_link_translation( (int) $english_menu->ID, $lang, (int) $menu_id );
			printf( "polylang: created %s menu (ID %d)\n", $lang, $menu_id );
		}

		// The header finds a menu by this marker, not by slug; without it the
		// language-scoped lookup returns nothing and the header renders no nav.
		if ( defined( 'PEDIMENT_CHILD_PRIMARY_NAV_MARKER' ) ) {
			update_post_meta( $menu_id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );
		}
	}
}

flush_rewrite_rules( false );
echo "polylang: ready\n";
