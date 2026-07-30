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
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WORKATION_CONSENT_COOKIE' ) ) {
	define( 'WORKATION_CONSENT_COOKIE', 'wc_consent' );
}
if ( ! defined( 'WORKATION_CONSENT_VERSION' ) ) {
	define( 'WORKATION_CONSENT_VERSION', 1 );
}
if ( ! defined( 'WORKATION_CONSENT_DAYS' ) ) {
	define( 'WORKATION_CONSENT_DAYS', 365 );
}
if ( ! defined( 'WORKATION_POSTHOG_KEY' ) ) {
	define( 'WORKATION_POSTHOG_KEY', '' );
}
if ( ! defined( 'WORKATION_POSTHOG_HOST' ) ) {
	define( 'WORKATION_POSTHOG_HOST', 'https://eu.i.posthog.com' );
}

/**
 * Hosts whose iframes are gated behind the Functional category.
 *
 * Matched as substrings of the iframe src host, so subdomains and TLD variants
 * (komoot.com / komoot.de, maps.google.com) are all covered.
 *
 * @return string[]
 */
function workation_consent_gated_hosts() {
	return array( 'komoot.', 'maps.google.' );
}

/**
 * Whether an iframe src points at a gated third-party provider.
 *
 * @param string $src Iframe src URL.
 * @return bool
 */
function workation_consent_is_external_embed( $src ) {
	$host = wp_parse_url( $src, PHP_URL_HOST );
	if ( ! $host ) {
		return false;
	}
	$host = strtolower( $host );
	foreach ( workation_consent_gated_hosts() as $needle ) {
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
function workation_consent_provider_label( $src ) {
	$host = strtolower( (string) wp_parse_url( $src, PHP_URL_HOST ) );
	if ( false !== strpos( $host, 'komoot.' ) ) {
		return 'Komoot';
	}
	if ( false !== strpos( $host, 'maps.google.' ) ) {
		return 'Google Maps';
	}
	return __( 'this provider', 'pediment-child' );
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
function workation_consent_defuse_iframes( $content ) {
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
			if ( ! workation_consent_is_external_embed( $src ) ) {
				return $m[0];
			}

			// Swap src -> data-consent-src so the browser never requests it.
			$new_attrs = preg_replace(
				'/\ssrc\s*=\s*("|\').*?\1/i',
				' data-consent-src="' . esc_url( $src ) . '" data-consent-category="functional"',
				$attrs,
				1
			);
			$iframe    = '<iframe' . $new_attrs . '>' . $m[2] . '</iframe>';
			$provider  = workation_consent_provider_label( $src );

			return '<div class="wc-consent-embed" data-consent-category="functional">'
				. $iframe
				. '<div class="wc-consent-embed__overlay">'
				. '<p class="wc-consent-embed__text">'
				. sprintf(
					/* translators: %s: third-party provider name. */
					esc_html__( 'This content is hosted by %s. Loading it sends data to that provider.', 'workation' ),
					esc_html( $provider )
				)
				. '</p>'
				. '<button type="button" class="wc-consent-embed__load">'
				. esc_html__( 'Load external content', 'workation' )
				. '</button>'
				. '</div>'
				. '</div>';
		},
		$content
	);
}

/**
 * Defuse gated iframes at the_content / render_block render time.
 *
 * @param string $content Block or post content HTML.
 * @return string
 */
function workation_consent_filter_content( $content ) {
	return workation_consent_defuse_iframes( $content );
}
add_filter( 'the_content', 'workation_consent_filter_content', 20 );

/**
 * Defuse gated iframes inside core/html blocks via the render_block hook.
 *
 * Core/html blocks (the Komoot embeds are raw HTML blocks)
 * do not pass through the_content's wpautop path the same way.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function workation_consent_filter_block( $block_content, $block ) {
	if ( isset( $block['blockName'] ) && 'core/html' === $block['blockName'] ) {
		return workation_consent_defuse_iframes( $block_content );
	}
	return $block_content;
}
add_filter( 'render_block', 'workation_consent_filter_block', 20, 2 );

/**
 * Enqueue the consent manager (CSS + JS) on every front-end view, and pass its
 * runtime config (cookie name, schema version, PostHog key) to JS.
 */
function workation_consent_enqueue() {
	$css_path = get_stylesheet_directory() . '/assets/css/consent.css';
	wp_enqueue_style(
		'workation-castle-consent',
		get_stylesheet_directory_uri() . '/assets/css/consent.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : wp_get_theme()->get( 'Version' )
	);

	$js_path = get_stylesheet_directory() . '/assets/js/consent.js';
	wp_enqueue_script(
		'workation-castle-consent',
		get_stylesheet_directory_uri() . '/assets/js/consent.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : wp_get_theme()->get( 'Version' ),
		true
	);

	$config = array(
		'cookieName'  => WORKATION_CONSENT_COOKIE,
		'version'     => (int) WORKATION_CONSENT_VERSION,
		'days'        => (int) WORKATION_CONSENT_DAYS,
		'posthogKey'  => (string) WORKATION_POSTHOG_KEY,
		'posthogHost' => (string) WORKATION_POSTHOG_HOST,
	);
	wp_scripts()->add_data(
		'workation-castle-consent',
		'data',
		'var wcConsentConfig = ' . wp_json_encode( $config ) . ';'
	);
}
add_action( 'wp_enqueue_scripts', 'workation_consent_enqueue' );
