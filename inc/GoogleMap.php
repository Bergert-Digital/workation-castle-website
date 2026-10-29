<?php
/**
 * Google Map block: renders a Google Maps embed as a .wc-map figure.
 *
 * The iframe is built here, not stored in post content, so pages hold no raw
 * HTML. The consent layer (inc/Consent.php) defuses it at the_content time
 * until the visitor allows Functional content.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Markup for one workation/google-map block.
 *
 * @param array $attributes Block attributes (url, title).
 * @return string Empty when the URL is not a Google Maps link.
 */
function workation_google_map_chrome( array $attributes ): string {
	$url = (string) ( $attributes['url'] ?? '' );
	if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! workation_consent_is_google_maps( $url ) ) {
		return '';
	}

	return sprintf(
		'<figure %s><iframe title="%s" src="%s" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></figure>',
		get_block_wrapper_attributes( array( 'class' => 'wc-map' ) ),
		esc_attr( (string) ( $attributes['title'] ?? '' ) ),
		esc_url( $url )
	);
}
