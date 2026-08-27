<?php
/**
 * The site was rebuilt with a new, translated URL structure. Every legacy path
 * that Google still has indexed — including the per-language ones — must 301 to
 * its new equivalent IN THE SAME LANGUAGE, so ranking and inbound links carry
 * over and non-English URLs never consolidate onto the English page.
 *
 * These tests exercise Workation\Redirects::target_for(), the pure resolver
 * behind the template_redirect handler.
 *
 * @package Workation
 */

use Workation\Redirects;

class RedirectsTest extends WP_UnitTestCase {

	public function test_english_legacy_paths_map_to_new_urls() {
		$this->assertSame( home_url( '/guide/faq/' ), Redirects::target_for( '/faq/' ) );
		$this->assertSame( home_url( '/guide/catering/' ), Redirects::target_for( '/catering/' ) );
		$this->assertSame( home_url( '/imprint/' ), Redirects::target_for( '/attribution/' ) );
		$this->assertSame( home_url( '/ways-to-stay/team-retreats/' ), Redirects::target_for( '/team-retreats/' ) );
	}

	/**
	 * A translated legacy path must land on the same-language page, never on the
	 * English one. This is the core regression: /de/faq/ used to reach /guide/faq/.
	 */
	public function test_translated_legacy_paths_stay_in_their_language() {
		$this->assertSame( home_url( '/de/guide/faq/' ), Redirects::target_for( '/de/faq/' ) );
		$this->assertSame( home_url( '/nl/gids/faq/' ), Redirects::target_for( '/nl/faq/' ) );
		$this->assertSame( home_url( '/it/guida/servizio-di-ristorazione/' ), Redirects::target_for( '/it/catering/' ) );
		$this->assertSame( home_url( '/nl/gids/catering/' ), Redirects::target_for( '/nl/catering/' ) );
		$this->assertSame( home_url( '/de/aufenthalte/team-retreats/' ), Redirects::target_for( '/de/team-retreats/' ) );
		$this->assertSame( home_url( '/nl/verblijfsvormen/teamretraites/' ), Redirects::target_for( '/nl/retraites/' ) );
	}

	/** Renamed translated slugs (privacy, waste, contact, imprint) must resolve. */
	public function test_renamed_translated_slugs_resolve() {
		$this->assertSame( home_url( '/nl/gids/afvalverwerking/' ), Redirects::target_for( '/nl/gids/afvalverwijdering/' ) );
		$this->assertSame( home_url( '/fr/guide/traitement-des-dechets/' ), Redirects::target_for( '/fr/guide/elimination-des-dechets/' ) );
		$this->assertSame( home_url( '/de/guide/catering/' ), Redirects::target_for( '/de/verpflegung/' ) );
		$this->assertSame( home_url( '/it/guida/faq/' ), Redirects::target_for( '/it/domande-frequenti/' ) );
		$this->assertSame( home_url( '/nl/privacyverklaring/' ), Redirects::target_for( '/nl/privacybeleid/' ) );
		$this->assertSame( home_url( '/de/kontakt/' ), Redirects::target_for( '/de/kontaktieren-sie-uns/' ) );
	}

	/** Legacy activity URLs map to the matching per-language activity page. */
	public function test_translated_activity_paths_resolve() {
		$this->assertSame( home_url( '/activities/canyon-tour-in-val-sanagra/' ), Redirects::target_for( '/val-sanagra-canyon/' ) );
		$this->assertSame( home_url( '/nl/activities/canyontocht-in-val-sanagra/' ), Redirects::target_for( '/nl/kloof-val-sanagra/' ) );
		$this->assertSame( home_url( '/fr/activities/randonnee-du-canyon-en-val-sanagra/' ), Redirects::target_for( '/fr/canyon-de-val-sanagra/' ) );
	}

	/** A path that is not a legacy key returns null (no redirect for live pages). */
	public function test_live_and_unknown_paths_return_null() {
		$this->assertNull( Redirects::target_for( '/de/guide/faq/' ) );
		$this->assertNull( Redirects::target_for( '/nl/gids/catering/' ) );
		$this->assertNull( Redirects::target_for( '/' ) );
		$this->assertNull( Redirects::target_for( '/something-random/' ) );
	}

	/** Trailing slash and query string must not defeat the match. */
	public function test_match_ignores_trailing_slash_and_query() {
		$expected = home_url( '/de/guide/faq/' );
		$this->assertSame( $expected, Redirects::target_for( '/de/faq' ) );
		$this->assertSame( $expected, Redirects::target_for( '/de/faq/?utm_source=google' ) );
	}

	/** Every target is a same-language, root-relative path (data integrity). */
	public function test_every_target_is_root_relative_and_language_consistent() {
		foreach ( Redirects::MAP as $key => $target ) {
			$this->assertStringStartsWith( '/', $target, "$key target must be root-relative" );
			foreach ( array( 'de', 'fr', 'it', 'nl' ) as $lang ) {
				if ( 0 === strpos( $key, "$lang/" ) ) {
					$this->assertStringStartsWith( "/$lang/", $target, "$key must map within /$lang/" );
				}
			}
		}
	}
}
