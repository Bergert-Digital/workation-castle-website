<?php
/**
 * Language-aware booking (Holidu) links.
 *
 * Every "Check availability" call to action points at the Holidu booking
 * partner (workationcastle.holiduhost.com). That partner takes a `language`
 * query parameter so the booking flow opens in the visitor's language, but the
 * theme's markup hardcodes the bare URL everywhere — in the header/footer
 * template parts, in inline pattern anchors, and as the workation/* blocks'
 * primaryUrl. None of them carried the language.
 *
 * This file appends `language=<slug>` (the current Polylang language) to those
 * links at render time, so no stored content has to be edited. It mirrors
 * inc/LocalizeLinks.php: a render_block filter rewrites the booking hrefs, a
 * cheap sentinel keeps every other block fast, and external non-Holidu links
 * pass straight through. The availability form is handled separately — a GET
 * form drops its action's query string, so it carries the language as a hidden
 * field instead (see inc/AvailabilityForm.php).
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Host of the Holidu booking partner. Any href on this host is language-tagged.
 */
const WORKATION_BOOKING_HOST = 'holiduhost.com';

/**
 * Current Polylang language slug, or '' when Polylang is inactive.
 *
 * @return string
 */
function workation_booking_language() {
	$slug = function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';

	/**
	 * Filter the language slug appended to booking links.
	 *
	 * Defaults to the current Polylang language. Returning '' disables the
	 * parameter (and is the standard when Polylang is inactive).
	 *
	 * @param string $slug Language slug, e.g. "fr".
	 */
	return (string) apply_filters( 'workation_booking_language', $slug );
}

/**
 * Append `language=<current slug>` to a Holidu booking URL.
 *
 * Leaves anything that is not a Holidu URL untouched, and is idempotent:
 * add_query_arg() replaces an existing `language` value rather than duplicating
 * it, so re-running the rewrite is safe.
 *
 * @param string $url A URL from body content or a block attribute.
 * @return string The URL with the language parameter, or the original.
 */
function workation_localize_booking_url( $url ) {
	$url  = (string) $url;
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $host || false === stripos( $host, WORKATION_BOOKING_HOST ) ) {
		return $url;
	}

	$lang = workation_booking_language();
	if ( '' === $lang ) {
		return $url;
	}

	return add_query_arg( 'language', $lang, $url );
}

/**
 * Rewrite Holidu booking hrefs in rendered block HTML to carry the language.
 *
 * Hooked on render_block so it covers both inline anchors (header, footer,
 * contact) and the dynamic workation/* blocks' primaryUrl. A cheap sentinel
 * keeps every other block fast.
 *
 * @param string $content Rendered block HTML.
 * @return string HTML with booking links language-tagged.
 */
function workation_localize_booking_links( $content ) {
	if ( ! is_string( $content ) || false === strpos( $content, WORKATION_BOOKING_HOST ) ) {
		return $content;
	}

	return (string) preg_replace_callback(
		'/\bhref="(https?:\/\/[^"]*' . preg_quote( WORKATION_BOOKING_HOST, '/' ) . '[^"]*)"/i',
		static function ( $matches ) {
			$localized = workation_localize_booking_url( html_entity_decode( $matches[1] ) );
			return 'href="' . esc_url( $localized ) . '"';
		},
		$content
	);
}
add_filter( 'render_block', 'workation_localize_booking_links', 20 );
