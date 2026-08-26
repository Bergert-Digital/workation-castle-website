<?php
/**
 * Localize hardcoded internal links in body content.
 *
 * Patterns bake internal links into block markup as root-relative English
 * paths — both as inline anchors (e.g. contact.php's /privacy-policy/) and as
 * block attributes rendered by inc/WorkationSections.php (e.g. the closing
 * CTA's secondaryUrl "/contact-us/"). Polylang never rewrites those, so on a
 * translated page every one of them leaks to the English page. The header and
 * footer already avoid this by building their links in PHP through Polylang
 * (see workation_footer_localized_url()); this file does the same for body
 * content, at render time, so no page's stored content has to be edited.
 *
 * The signal is precise: a leaking link is always root-relative ("/contact-us/")
 * while every already-correct link (nav, footer, activity back-links) is an
 * absolute, language-prefixed URL. Rewriting only root-relative internal hrefs
 * therefore touches the leaks and nothing else, and is idempotent because the
 * rewrite yields an absolute URL that no longer matches.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve a root-relative internal path to its permalink in the current language.
 *
 * Leaves anything that is not an internal, navigational, root-relative path
 * untouched: external URLs, mailto:/tel:, in-page #fragments, asset paths under
 * /wp-, the bare home "/", and the empty string all pass straight through.
 *
 * For an internal path the page is found by its (English) path, with a leaf-slug
 * fallback so a stale slug like "/catering/" still resolves to the real
 * guide/catering page. The page's translation for the language being rendered is
 * then followed (mirroring workation_footer_localized_url()), and any query
 * string or fragment on the original URL is preserved.
 *
 * @param string $url A URL or root-relative path from body content.
 * @return string Localized absolute URL, or the original when it does not resolve.
 */
function workation_localize_url( $url ) {
	$url = (string) $url;

	// Only touch root-relative paths; skip the bare home link and asset paths.
	if ( '' === $url || '/' === $url || '/' !== $url[0] || 0 === strpos( $url, '/wp-' ) ) {
		return $url;
	}

	// Split off ?query and #fragment so they survive the permalink swap.
	$suffix = '';
	$path   = $url;
	$cut    = strcspn( $path, '?#' );
	if ( $cut < strlen( $path ) ) {
		$suffix = substr( $path, $cut );
		$path   = substr( $path, 0, $cut );
	}

	$path = trim( $path, '/' );
	if ( '' === $path ) {
		return $url;
	}

	$page = get_page_by_path( $path );
	if ( ! $page ) {
		// Stale or reparented slug: fall back to matching the final segment.
		$segments = explode( '/', $path );
		$leaf     = end( $segments );
		$matches  = get_posts(
			array(
				'post_type'        => 'page',
				'name'             => $leaf,
				'post_status'      => 'publish',
				'numberposts'      => 1,
				'suppress_filters' => false,
			)
		);
		if ( ! empty( $matches ) ) {
			$page = $matches[0];
		}
	}

	if ( ! $page ) {
		return $url;
	}

	// Follow the translation for the language being rendered, when Polylang is active.
	if ( function_exists( 'pll_get_post' ) ) {
		$translated = pll_get_post( $page->ID );
		if ( $translated ) {
			$page = get_post( $translated );
		}
	}

	$permalink = get_permalink( $page );
	if ( ! $permalink ) {
		return $url;
	}

	return $permalink . $suffix;
}

/**
 * Rewrite root-relative internal links in rendered block HTML to the current
 * language. Hooked on render_block so it covers both inline anchors and the
 * dynamic workation/* blocks; a cheap sentinel keeps every other block fast.
 *
 * @param string $content Rendered block HTML.
 * @return string HTML with internal body links localized.
 */
function workation_localize_content_links( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, 'href="/' ) ) {
		return $content;
	}

	return (string) preg_replace_callback(
		'/\bhref="(\/[^"]*)"/',
		static function ( $matches ) {
			$localized = workation_localize_url( $matches[1] );
			if ( $localized === $matches[1] ) {
				return $matches[0];
			}
			return 'href="' . esc_url( $localized ) . '"';
		},
		$content
	);
}
add_filter( 'render_block', 'workation_localize_content_links', 20 );
