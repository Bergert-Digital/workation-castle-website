<?php
/**
 * Legacy URL redirects: 301 retired workationcastle.com paths to their new homes.
 *
 * The site was rebuilt with a new page structure; a few old URLs changed slug,
 * moved under a new parent, or were folded into another page. This maps each
 * retired path to its closest current equivalent so inbound links and search
 * results keep working. Pages whose slug is unchanged need no entry — they
 * resolve normally.
 *
 * @package Workation
 */

namespace Workation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end 301 redirects for retired legacy paths.
 */
class Redirects {

	/**
	 * Map of retired path => current path.
	 *
	 * Keys are root-relative and compared without surrounding slashes, so a
	 * request matches with or without a trailing slash. Values are passed to
	 * home_url(), so they should be root-relative with a leading slash.
	 *
	 * @var array<string,string>
	 */
	const MAP = array(
		'team-retreats'      => '/ways-to-stay/team-retreats/',
		'retreats'           => '/ways-to-stay/team-retreats/',
		'val-sanagra-canyon' => '/activities/canyon-tour-in-val-sanagra/',
		'house-registration' => '/check-in/',
		'attribution'        => '/imprint/',
		'faq'                => '/guide/faq/',
	);

	/**
	 * Hook the redirect check onto template_redirect.
	 *
	 * Priority 1 runs it before core's redirect_canonical (priority 10), so a
	 * retired path is sent to its mapped target rather than 404-guessed.
	 */
	public static function register(): void {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	/**
	 * 301-redirect the current request when its path is a retired legacy path.
	 */
	public static function maybe_redirect(): void {
		if ( is_admin() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$path = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return;
		}

		$key = trim( $path, '/' );
		if ( '' === $key || ! isset( self::MAP[ $key ] ) ) {
			return;
		}

		wp_safe_redirect( home_url( self::MAP[ $key ] ), 301 );
		exit;
	}
}
