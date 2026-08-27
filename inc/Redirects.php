<?php
/**
 * Legacy URL redirects: 301 retired paths to their new homes, per language.
 *
 * The site was rebuilt with a new, translated page structure; many old URLs
 * changed slug or moved under a new parent (e.g. /faq/ -> /guide/faq/,
 * /de/verpflegung/ -> /de/guide/catering/). This maps each retired path to its
 * current equivalent IN THE SAME LANGUAGE, so inbound links and search rankings
 * carry over and a translated legacy URL never lands on the English page.
 *
 * Keys are the request path without surrounding slashes (so a request matches
 * with or without a trailing slash, and query strings are ignored). Values are
 * passed to home_url(), so they are root-relative with a leading slash and, for
 * a translated page, carry the language prefix. Pages whose slug is unchanged
 * need no entry — they resolve normally.
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
	 * Map of retired path => current path (both root-relative; see file header).
	 *
	 * @var array<string,string>
	 */
	const MAP = array(
		// English legacy slugs.
		'catering'                               => '/guide/catering/',
		'faq'                                    => '/guide/faq/',
		'team-retreats'                          => '/ways-to-stay/team-retreats/',
		'retreats'                               => '/ways-to-stay/team-retreats/',
		'attribution'                            => '/imprint/',
		'house-registration'                     => '/check-in/',
		'val-sanagra-canyon'                     => '/activities/canyon-tour-in-val-sanagra/',

		// German (de).
		'de/faq'                                 => '/de/guide/faq/',
		'de/verpflegung'                         => '/de/guide/catering/',
		'de/leitfaden'                           => '/de/guide/',
		'de/leitfaden/ankunft'                   => '/de/guide/anreise/',
		'de/leitfaden/karte'                     => '/de/guide/karte/',
		'de/leitfaden/abfallentsorgung'          => '/de/guide/abfallentsorgung/',
		'de/leitfaden/casa-galbiga'              => '/de/guide/casa-galbiga/',
		'de/team-retreats'                       => '/de/aufenthalte/team-retreats/',
		'de/rueckzugsorte'                       => '/de/aufenthalte/team-retreats/',
		'de/anmeldung'                           => '/de/check-in/',
		'de/hausregistrierung'                   => '/de/check-in/',
		'de/datenschutzbestimmungen'             => '/de/datenschutzerklaerung/',
		'de/kontaktieren-sie-uns'                => '/de/kontakt/',
		'de/namensnennung'                       => '/de/impressum/',
		'de/rueckmeldung'                        => '/de/feedback/',
		'de/val-sanagra-schlucht'                => '/de/activities/canyon-tour-im-val-sanagra/',

		// French (fr).
		'fr/faq'                                 => '/fr/guide/faq/',
		'fr/activites'                           => '/fr/activities/',
		'fr/guide/carte'                         => '/fr/guide/plan/',
		'fr/guide/elimination-des-dechets'       => '/fr/guide/traitement-des-dechets/',
		'fr/attribution'                         => '/fr/mentions-legales/',
		'fr/impression'                          => '/fr/mentions-legales/',
		'fr/inscription-au-registre-du-commerce' => '/fr/mentions-legales/',
		'fr/contactez-nous'                      => '/fr/contact/',
		'fr/inscription'                         => '/fr/check-in/',
		'fr/retour-dinformation'                 => '/fr/feedback/',
		'fr/retraites'                           => '/fr/facons-de-sejourner/retraites-dequipe/',
		'fr/retraites-dequipe'                   => '/fr/facons-de-sejourner/retraites-dequipe/',
		'fr/canyon-de-val-sanagra'               => '/fr/activities/randonnee-du-canyon-en-val-sanagra/',

		// Italian (it).
		'it/catering'                            => '/it/guida/servizio-di-ristorazione/',
		'it/domande-frequenti'                   => '/it/guida/faq/',
		'it/attivita'                            => '/it/activities/',
		'it/attribuzione'                        => '/it/note-legali/',
		'it/impronta'                            => '/it/note-legali/',
		'it/informativa-sulla-privacy'           => '/it/privacy-policy/',
		'it/contattaci'                          => '/it/contatti/',
		'it/registrazione'                       => '/it/check-in/',
		'it/registrazione-della-casa'            => '/it/check-in/',
		'it/ritiri'                              => '/it/come-soggiornare/ritiri-aziendali/',
		'it/ritiri-di-squadra'                   => '/it/come-soggiornare/ritiri-aziendali/',
		'it/canyon-della-val-sanagra'            => '/it/activities/tour-del-canyon-in-val-sanagra/',

		// Dutch (nl).
		'nl/faq'                                 => '/nl/gids/faq/',
		'nl/catering'                            => '/nl/gids/catering/',
		'nl/gids/afvalverwijdering'              => '/nl/gids/afvalverwerking/',
		'nl/gids/kaart'                          => '/nl/gids/plattegrond/',
		'nl/afdruk'                              => '/nl/colofon/',
		'nl/naamsvermelding'                     => '/nl/colofon/',
		'nl/privacybeleid'                       => '/nl/privacyverklaring/',
		'nl/neem-contact-met-ons-op'             => '/nl/contact/',
		'nl/huis-registratie'                    => '/nl/check-in/',
		'nl/retraites'                           => '/nl/verblijfsvormen/teamretraites/',
		'nl/team-retraites'                      => '/nl/verblijfsvormen/teamretraites/',
		'nl/kloof-val-sanagra'                   => '/nl/activities/canyontocht-in-val-sanagra/',
	);

	/**
	 * Hook the redirect check onto template_redirect.
	 *
	 * Priority 1 runs it before core's redirect_canonical (priority 10), so a
	 * retired path is sent to its mapped target rather than 404-guessed onto the
	 * default-language page.
	 */
	public static function register(): void {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	/**
	 * Resolve a request path to its 301 target, or null when it is not a legacy
	 * path. Pure (no side effects) so it can be unit-tested; the trailing slash
	 * and any query string are ignored.
	 *
	 * @param string $request_path Raw request path (may include a query string).
	 * @return string|null Absolute redirect URL, or null when no mapping applies.
	 */
	public static function target_for( string $request_path ): ?string {
		$path = wp_parse_url( $request_path, PHP_URL_PATH );
		if ( ! is_string( $path ) ) {
			return null;
		}

		$key = trim( $path, '/' );
		if ( '' === $key || ! isset( self::MAP[ $key ] ) ) {
			return null;
		}

		return home_url( self::MAP[ $key ] );
	}

	/**
	 * 301-redirect the current request when its path is a retired legacy path.
	 */
	public static function maybe_redirect(): void {
		if ( is_admin() || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}

		$target = self::target_for( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		if ( null === $target ) {
			return;
		}

		wp_safe_redirect( $target, 301 );
		exit;
	}
}
