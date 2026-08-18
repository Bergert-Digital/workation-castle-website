<?php
/**
 * The footer template part is static HTML, so its copy and links are expanded
 * at render time. These tests lock in that behaviour: labels become
 * translatable, internal links resolve to a real page, and unrelated core/html
 * blocks pass through untouched.
 *
 * @package Workation
 */

class FooterRenderTest extends PatternTestCase {

	private function make_page( string $slug ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_title'  => ucfirst( $slug ),
				'post_status' => 'publish',
			)
		);
	}

	public function test_localized_url_links_to_the_matching_page() {
		$page_id = $this->make_page( 'ways-to-stay' );

		$this->assertSame(
			get_permalink( $page_id ),
			workation_footer_localized_url( 'ways-to-stay' )
		);
	}

	public function test_localized_url_falls_back_to_the_path_when_no_page_exists() {
		$this->assertSame(
			home_url( '/reviews/' ),
			workation_footer_localized_url( 'reviews' )
		);
	}

	public function test_render_tokens_translates_labels_and_resolves_links() {
		$page_id = $this->make_page( 'imprint' );

		$content = '<div class="foot-col"><h4>%WC_T_EXPLORE%</h4>'
			. '<a href="%WC_URL:imprint%">%WC_T_IMPRINT%</a></div>';

		$rendered = workation_footer_render_tokens( $content );

		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page_id ) ) . '"', $rendered );
		$this->assertStringContainsString( '>Imprint<', $rendered );
		$this->assertStringNotContainsString( '%WC_T_', $rendered );
		$this->assertStringNotContainsString( '%WC_URL:', $rendered );
	}

	public function test_render_tokens_fills_the_copyright_year() {
		$rendered = workation_footer_render_tokens( '<span>%WC_COPYRIGHT%</span>' );

		$this->assertStringContainsString( (string) gmdate( 'Y' ), $rendered );
		$this->assertStringContainsString( 'Workation Castle', $rendered );
		$this->assertStringNotContainsString( '%WC_COPYRIGHT%', $rendered );
	}

	public function test_render_tokens_leaves_unrelated_html_blocks_untouched() {
		$html = '<p>An ordinary core/html block with no footer tokens.</p>';

		$this->assertSame( $html, workation_footer_render_tokens( $html ) );
	}

	public function test_not_found_markup_links_home_with_translatable_copy() {
		$markup = workation_not_found_markup();

		$this->assertStringContainsString( '>Page not found<', $markup );
		$this->assertStringContainsString( 'Back to the homepage', $markup );
		$this->assertStringContainsString( 'href="' . esc_url( home_url( '/' ) ) . '"', $markup );
		$this->assertStringContainsString( 'class="wc-wrap not-found"', $markup );
	}

	public function test_footer_part_uses_tokens_not_hardcoded_english() {
		$footer = file_get_contents( $this->theme_dir() . '/parts/footer.html' );

		$this->assertStringContainsString( '%WC_LANG_SWITCHER%', $footer );
		$this->assertStringContainsString( '%WC_URL:ways-to-stay%', $footer );
		$this->assertStringNotContainsString( 'Ways to stay', $footer );
		$this->assertStringNotContainsString( 'href="/imprint/"', $footer );
		$this->assertStringNotContainsString( 'href="#"', $footer );
	}
}
