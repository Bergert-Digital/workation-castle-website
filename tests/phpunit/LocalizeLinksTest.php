<?php
/**
 * Body-content links are baked into block markup as hardcoded, root-relative
 * English paths (e.g. the closing CTA's /contact-us/). Polylang never rewrites
 * them, so on a translated page they leak to the English page. These tests lock
 * in the render-time fix: an internal root-relative href resolves to the
 * matching page (in the current language, via Polylang) while every other kind
 * of URL passes through untouched.
 *
 * @package Workation
 */

class LocalizeLinksTest extends PatternTestCase {

	private function make_page( string $slug, int $parent = 0 ): int {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_title'  => ucfirst( $slug ),
				'post_status' => 'publish',
				'post_parent' => $parent,
			)
		);
	}

	/** External and non-navigational URLs must never be touched. */
	public function test_leaves_external_and_special_urls_untouched() {
		$untouched = array(
			'https://workationcastle.holiduhost.com/',
			'mailto:info@workationcastle.com',
			'tel:+393487431408',
			'#controller',
			'/wp-content/themes/workation/assets/pdf/C112_Winter_2026.pdf',
			'/',
			'',
		);

		foreach ( $untouched as $url ) {
			$this->assertSame( $url, workation_localize_url( $url ), $url );
		}
	}

	public function test_resolves_a_root_relative_path_to_the_matching_page() {
		$page_id = $this->make_page( 'contact-us' );

		$this->assertSame(
			get_permalink( $page_id ),
			workation_localize_url( '/contact-us/' )
		);
	}

	public function test_resolves_a_nested_path() {
		$guide   = $this->make_page( 'guide' );
		$arrival = $this->make_page( 'arrival', $guide );

		$this->assertSame(
			get_permalink( $arrival ),
			workation_localize_url( '/guide/arrival/' )
		);
	}

	public function test_preserves_query_and_fragment() {
		$page_id = $this->make_page( 'photos' );
		$base    = get_permalink( $page_id );

		$this->assertSame( $base . '?filter=workspace', workation_localize_url( '/photos/?filter=workspace' ) );
		$this->assertSame( $base . '#top', workation_localize_url( '/photos/#top' ) );
	}

	/** The stale /catering/ slug must resolve to the real guide/catering page. */
	public function test_resolves_a_stale_slug_by_its_leaf() {
		$guide    = $this->make_page( 'guide' );
		$catering = $this->make_page( 'catering', $guide );

		$this->assertSame(
			get_permalink( $catering ),
			workation_localize_url( '/catering/' )
		);
	}

	public function test_returns_the_original_when_no_page_matches() {
		$this->assertSame( '/nowhere/', workation_localize_url( '/nowhere/' ) );
	}

	public function test_content_filter_rewrites_internal_body_links() {
		$page_id = $this->make_page( 'contact-us' );

		$html    = '<a class="wc-btn" href="/contact-us/">Ask for a custom offer</a>';
		$rendered = workation_localize_content_links( $html );

		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page_id ) ) . '"', $rendered );
		$this->assertStringNotContainsString( 'href="/contact-us/"', $rendered );
	}

	public function test_content_filter_leaves_external_links_untouched() {
		$html = '<a href="https://workationcastle.holiduhost.com/">Book</a>'
			. '<a href="mailto:info@workationcastle.com">Mail</a>';

		$this->assertSame( $html, workation_localize_content_links( $html ) );
	}

	public function test_content_filter_passes_through_content_without_internal_links() {
		$html = '<p>No links here at all.</p>';

		$this->assertSame( $html, workation_localize_content_links( $html ) );
	}

	/**
	 * The catering link must use the canonical guide/catering path, not the
	 * stale /catering/ slug that 301s to the English page.
	 */
	public function test_patterns_use_the_canonical_catering_path() {
		$faq = file_get_contents( $this->theme_dir() . '/patterns/faq.php' );
		$this->assertStringNotContainsString( 'href=\"/catering/\"', $faq );
		$this->assertStringContainsString( 'href=\"/guide/catering/\"', $faq );

		$guide = file_get_contents( $this->theme_dir() . '/patterns/guide.php' );
		$this->assertStringNotContainsString( '"linkUrl":"/catering/"', $guide );
		$this->assertStringContainsString( '"linkUrl":"/guide/catering/"', $guide );
	}
}
