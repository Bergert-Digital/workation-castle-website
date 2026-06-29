<?php
/**
 * Consent: GDPR consent manager wiring.
 *
 * Gates non-essential third-party resources behind opt-in consent. The
 * client-side manager (assets/js/consent.js) does the UI and persistence; this
 * file does the server-side half: it "defuses" gated <iframe>s before they
 * reach the browser (moving src -> data-consent-src so the request never fires)
 * and enqueues the manager with its config.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'PEDIMENT_CHILD_CONSENT_COOKIE' ) ) {
	define( 'PEDIMENT_CHILD_CONSENT_COOKIE', 'wc_consent' );
}
if ( ! defined( 'PEDIMENT_CHILD_CONSENT_VERSION' ) ) {
	define( 'PEDIMENT_CHILD_CONSENT_VERSION', 1 );
}
if ( ! defined( 'PEDIMENT_CHILD_CONSENT_DAYS' ) ) {
	define( 'PEDIMENT_CHILD_CONSENT_DAYS', 365 );
}
if ( ! defined( 'PEDIMENT_CHILD_POSTHOG_KEY' ) ) {
	define( 'PEDIMENT_CHILD_POSTHOG_KEY', '' );
}
if ( ! defined( 'PEDIMENT_CHILD_POSTHOG_HOST' ) ) {
	define( 'PEDIMENT_CHILD_POSTHOG_HOST', 'https://eu.i.posthog.com' );
}

/**
 * Hosts whose iframes are gated behind the Functional category.
 *
 * Matched as substrings of the iframe src host, so subdomains and TLD variants
 * (komoot.com / komoot.de, maps.google.com) are all covered.
 *
 * @return string[]
 */
function pediment_child_consent_gated_hosts() {
	return array( 'komoot.', 'maps.google.' );
}

/**
 * Whether an iframe src points at a gated third-party provider.
 *
 * @param string $src Iframe src URL.
 * @return bool
 */
function pediment_child_consent_is_external_embed( $src ) {
	$host = wp_parse_url( $src, PHP_URL_HOST );
	if ( ! $host ) {
		return false;
	}
	$host = strtolower( $host );
	foreach ( pediment_child_consent_gated_hosts() as $needle ) {
		if ( false !== strpos( $host, $needle ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Human label for the provider behind a gated src.
 *
 * @param string $src Iframe src URL.
 * @return string
 */
function pediment_child_consent_provider_label( $src ) {
	$host = strtolower( (string) wp_parse_url( $src, PHP_URL_HOST ) );
	if ( false !== strpos( $host, 'komoot.' ) ) {
		return 'Komoot';
	}
	if ( false !== strpos( $host, 'maps.google.' ) ) {
		return 'Google Maps';
	}
	return 'this provider';
}

/**
 * Defuse gated third-party iframes in a content string.
 *
 * For each <iframe> whose src is a gated provider: move src -> data-consent-src,
 * add data-consent-category="functional", and wrap it in a .wc-consent-embed
 * placeholder with a "Load external content" button. Non-gated iframes and
 * iframe-free content are returned verbatim.
 *
 * @param string $content Rendered HTML.
 * @return string
 */
function pediment_child_consent_defuse_iframes( $content ) {
	if ( false === stripos( $content, '<iframe' ) ) {
		return $content;
	}

	return preg_replace_callback(
		'/<iframe\b([^>]*)>(.*?)<\/iframe>/is',
		function ( $m ) {
			$attrs = $m[1];
			if ( ! preg_match( '/\ssrc\s*=\s*("|\')(.*?)\1/i', $attrs, $src_m ) ) {
				return $m[0];
			}
			$src = $src_m[2];
			if ( ! pediment_child_consent_is_external_embed( $src ) ) {
				return $m[0];
			}

			// Swap src -> data-consent-src so the browser never requests it.
			$new_attrs = preg_replace(
				'/\ssrc\s*=\s*("|\').*?\1/i',
				' data-consent-src="' . esc_url( $src ) . '" data-consent-category="functional"',
				$attrs,
				1
			);
			$iframe   = '<iframe' . $new_attrs . '>' . $m[2] . '</iframe>';
			$provider = pediment_child_consent_provider_label( $src );

			return '<div class="wc-consent-embed" data-consent-category="functional">'
				. $iframe
				. '<div class="wc-consent-embed__overlay">'
				. '<p class="wc-consent-embed__text">'
				. sprintf(
					/* translators: %s: third-party provider name. */
					esc_html__( 'This content is hosted by %s. Loading it sends data to that provider.', 'pediment-child' ),
					esc_html( $provider )
				)
				. '</p>'
				. '<button type="button" class="wc-consent-embed__load">'
				. esc_html__( 'Load external content', 'pediment-child' )
				. '</button>'
				. '</div>'
				. '</div>';
		},
		$content
	);
}

/**
 * the_content / render_block hook: defuse gated iframes at render time.
 *
 * @param string $content Block or post content HTML.
 * @return string
 */
function pediment_child_consent_filter_content( $content ) {
	return pediment_child_consent_defuse_iframes( $content );
}
add_filter( 'the_content', 'pediment_child_consent_filter_content', 20 );

/**
 * render_block hook for core/html blocks (the Komoot embeds are raw HTML blocks,
 * which do not pass through the_content's wpautop path the same way).
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function pediment_child_consent_filter_block( $block_content, $block ) {
	if ( isset( $block['blockName'] ) && 'core/html' === $block['blockName'] ) {
		return pediment_child_consent_defuse_iframes( $block_content );
	}
	return $block_content;
}
add_filter( 'render_block', 'pediment_child_consent_filter_block', 20, 2 );
