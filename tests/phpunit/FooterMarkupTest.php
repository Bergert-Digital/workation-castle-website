<?php

/**
 * The footer is server-rendered so labels translate and links resolve to the
 * visitor's language. Link labels come from the pages' own (translated)
 * titles; URLs from the localized page resolver.
 */
class FooterMarkupTest extends WP_UnitTestCase {

	private function make_page( string $title, string $slug, int $parent = 0 ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_parent' => $parent,
				'post_status' => 'publish',
			)
		);
	}

	public function test_localized_page_url_resolves_nested_paths() {
		$guide   = $this->make_page( 'Guide', 'guide' );
		$arrival = $this->make_page( 'Arrival', 'arrival', $guide );

		$this->assertSame( get_permalink( $arrival ), workation_localized_page_url( 'guide/arrival' ) );
	}

	public function test_localized_page_url_falls_back_to_the_path() {
		$this->assertSame( home_url( '/reviews/' ), workation_localized_page_url( 'reviews' ) );
	}

	public function test_footer_links_pages_with_their_own_titles() {
		$id = $this->make_page( 'Bewertungen', 'reviews' );

		$html = workation_footer_markup();

		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $id ) ) . '"', $html );
		$this->assertStringContainsString( 'Bewertungen', $html );
	}

	public function test_footer_falls_back_to_default_labels_without_pages() {
		$html = workation_footer_markup();

		$this->assertStringContainsString( 'Reviews', $html );
		$this->assertStringContainsString( 'Arrival', $html );
	}

	public function test_footer_contains_booking_link_and_chrome() {
		$html = workation_footer_markup();

		$this->assertStringContainsString( 'class="wc-footer"', $html );
		$this->assertStringContainsString( 'https://workationcastle.holiduhost.com/', $html );
		$this->assertStringContainsString( 'Check availability', $html );
		$this->assertStringContainsString( 'wc-consent-settings-link', $html );
		$this->assertStringContainsString( 'logo-wordmark.svg', $html );
	}

	public function test_footer_copyright_year_is_dynamic() {
		$this->assertStringContainsString( '© ' . gmdate( 'Y' ) . ' Workation Castle', workation_footer_markup() );
	}

	public function test_footer_omits_castello_di_carlazzo() {
		$this->assertStringNotContainsString( 'Castello di Carlazzo', workation_footer_markup() );
	}

	public function test_footer_part_delegates_to_the_pattern() {
		$part = file_get_contents( dirname( __DIR__, 2 ) . '/parts/footer.html' );

		$this->assertStringContainsString( '"slug":"workation/footer"', $part );
		$this->assertStringNotContainsString( 'Check availability', $part );
	}
}
