<?php
/**
 * Dynamic rendering for the static footer template part.
 *
 * The parts/footer.html file is a core/html block and cannot run PHP, so its
 * copy would never reach the POT and its links would always point at the
 * default language. This file expands a small set of placeholder tokens at
 * render time: user-facing strings become translatable __() calls (scanned
 * into the POT because they live in inc/, not patterns/), internal links are
 * mapped to the current language, and the "Languages" column becomes a real
 * Polylang switcher instead of dead "#" anchors.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatable footer strings, keyed by their placeholder token.
 *
 * Kept as literal __() calls so `wp i18n make-pot` extracts them; the token
 * indirection lets parts/footer.html stay valid, designer-editable markup.
 *
 * @return array<string, string> Token => translated, HTML-escaped label.
 */
function workation_footer_labels() {
	return array(
		'%WC_T_TAGLINE%'      => esc_html__( 'Castello di Carlazzo — vacation homes and co-working between Lake Lugano and Lake Como.', 'workation' ),
		'%WC_T_EXPLORE%'      => esc_html__( 'Explore', 'workation' ),
		'%WC_T_VISIT%'        => esc_html__( 'Visit', 'workation' ),
		'%WC_T_LANGUAGES%'    => esc_html__( 'Languages', 'workation' ),
		'%WC_T_WAYS%'         => esc_html__( 'Ways to stay', 'workation' ),
		'%WC_T_GALLERY%'      => esc_html__( 'Gallery', 'workation' ),
		'%WC_T_REVIEWS%'      => esc_html__( 'Reviews', 'workation' ),
		'%WC_T_GETTING_HERE%' => esc_html__( 'Getting here', 'workation' ),
		'%WC_T_THINGS%'       => esc_html__( 'Things to do', 'workation' ),
		'%WC_T_CHECK_AVAIL%'  => esc_html__( 'Check availability', 'workation' ),
		'%WC_T_ASK_OFFER%'    => esc_html__( 'Ask for a custom offer', 'workation' ),
		'%WC_T_CONTACT%'      => esc_html__( 'Contact', 'workation' ),
		'%WC_T_FEEDBACK%'     => esc_html__( 'Feedback', 'workation' ),
		'%WC_T_IMPRINT%'      => esc_html__( 'Imprint', 'workation' ),
		'%WC_T_PRIVACY%'      => esc_html__( 'Privacy Policy', 'workation' ),
		'%WC_T_COOKIE%'       => esc_html__( 'Cookie settings', 'workation' ),
	);
}

/**
 * Map an internal footer path to its permalink in the current language.
 *
 * Mirrors workation_activities_page_url(): under Polylang the language versions
 * of a page can share a slug, so a hardcoded "/ways-to-stay/" always lands on
 * the default language. Resolve the page by its (English) path and follow the
 * translation for the language being rendered.
 *
 * @param string $path Language-neutral page path, e.g. "ways-to-stay/workations".
 * @return string Permalink for the current language, or a home-relative fallback.
 */
function workation_footer_localized_url( $path ) {
	$path = trim( $path, '/' );

	$page = get_page_by_path( $path );
	if ( $page && function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}
	if ( $page ) {
		return (string) get_permalink( $page );
	}

	return home_url( '/' . $path . '/' );
}

/**
 * Render the footer "Languages" column as a Polylang language switcher.
 *
 * Replaces the five dead "#" anchors with links to the current page in each
 * language, marking the active one. Returns an empty string when Polylang is
 * inactive so the column simply collapses rather than showing broken links.
 *
 * @return string Anchor markup for each active language.
 */
function workation_footer_language_switcher() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return '';
	}

	$languages = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_no_translation' => 0,
			'display_names_as'       => 'name',
		)
	);
	if ( empty( $languages ) || ! is_array( $languages ) ) {
		return '';
	}

	$out = '';
	foreach ( $languages as $language ) {
		$classes = 'foot-lang';
		if ( ! empty( $language['current_lang'] ) ) {
			$classes .= ' is-current';
		}
		$out .= sprintf(
			'<a href="%s" class="%s" lang="%s" hreflang="%s">%s</a>',
			esc_url( $language['url'] ),
			esc_attr( $classes ),
			esc_attr( $language['locale'] ),
			esc_attr( $language['slug'] ),
			esc_html( $language['name'] )
		);
	}

	return $out;
}

/**
 * Expand the footer placeholder tokens on the core/html block that carries them.
 *
 * Guarded by a cheap sentinel check so every other core/html block on the site
 * passes straight through. Runs alongside the %WORKATION_THEME_URI% filter in
 * functions.php; order does not matter as the token sets are disjoint.
 *
 * @param string $content Rendered block HTML.
 * @return string HTML with footer tokens resolved.
 */
function workation_footer_render_tokens( $content ) {
	if ( false === strpos( $content, '%WC_T_' )
		&& false === strpos( $content, '%WC_URL:' )
		&& false === strpos( $content, '%WC_LANG_SWITCHER%' )
		&& false === strpos( $content, '%WC_COPYRIGHT%' ) ) {
		return $content;
	}

	$content = strtr( $content, workation_footer_labels() );

	$content = preg_replace_callback(
		'/%WC_URL:([^%]+)%/',
		static function ( $matches ) {
			return esc_url( workation_footer_localized_url( $matches[1] ) );
		},
		$content
	);

	if ( false !== strpos( $content, '%WC_LANG_SWITCHER%' ) ) {
		$content = str_replace( '%WC_LANG_SWITCHER%', workation_footer_language_switcher(), $content );
	}

	if ( false !== strpos( $content, '%WC_COPYRIGHT%' ) ) {
		$year      = (int) gmdate( 'Y' );
		$copyright = sprintf(
			/* translators: %d: current year. */
			esc_html__( '© %d Workation Castle · Castello di Carlazzo, Italy', 'workation' ),
			$year
		);
		$content = str_replace( '%WC_COPYRIGHT%', $copyright, $content );
	}

	return $content;
}
add_filter( 'render_block_core/html', 'workation_footer_render_tokens' );

/**
 * The 404 message markup, for the workation/not-found pattern.
 *
 * Lives here rather than in the pattern file so the strings are scanned into
 * the POT (i18n:pot excludes patterns/).
 *
 * @return string
 */
function workation_not_found_markup() {
	return sprintf(
		'<div class="wc-wrap not-found">
	<h1>%s</h1>
	<p><a class="text-link" href="%s"><span class="arr">←</span>%s</a></p>
</div>',
		esc_html__( 'Page not found', 'workation' ),
		esc_url( home_url( '/' ) ),
		esc_html__( 'Back to the homepage', 'workation' )
	);
}
