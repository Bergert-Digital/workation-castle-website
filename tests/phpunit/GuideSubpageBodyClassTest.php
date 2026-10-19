<?php
/**
 * The guide's sub-pages (Arrival, Map, Casa Galbiga, FAQ, …) carry the
 * `wc-guide-subpage` body class in every language. The stylesheet keys their
 * anchor offsets, spacing and the steps card off it.
 *
 * Each language has its own guide page with its own ID and slug, so the guide
 * is identified by its default-language translation having the slug `guide`.
 * Polylang is not loaded in the test harness; the translated case maps the
 * page to its default-language translation through the
 * `workation_default_language_page_id` filter, as Polylang does at runtime.
 */
class GuideSubpageBodyClassTest extends WP_UnitTestCase {

	private function page( $slug, $parent = 0 ) {
		return self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $slug,
				'post_parent' => $parent,
			)
		);
	}

	public function test_child_of_the_guide_gets_the_class() {
		$guide   = $this->page( 'guide' );
		$arrival = $this->page( 'arrival', $guide );

		$this->go_to( get_permalink( $arrival ) );
		$this->assertContains( 'wc-guide-subpage', get_body_class() );
	}

	public function test_child_of_a_translated_guide_gets_the_class() {
		$guide    = $this->page( 'guide' );
		$guide_de = $this->page( 'guide-de' );
		$child    = $this->page( 'arrival-de', $guide_de );

		add_filter(
			'workation_default_language_page_id',
			function ( $source_id, $page_id ) use ( $guide, $guide_de ) {
				return $guide_de === $page_id ? $guide : $source_id;
			},
			10,
			2
		);

		$this->go_to( get_permalink( $child ) );
		$this->assertContains( 'wc-guide-subpage', get_body_class() );
	}

	public function test_the_guide_itself_does_not_get_the_class() {
		$guide = $this->page( 'guide' );

		$this->go_to( get_permalink( $guide ) );
		$this->assertNotContains( 'wc-guide-subpage', get_body_class() );
	}

	public function test_child_of_another_page_does_not_get_the_class() {
		$parent = $this->page( 'ways-to-stay' );
		$child  = $this->page( 'workations', $parent );

		$this->go_to( get_permalink( $child ) );
		$this->assertNotContains( 'wc-guide-subpage', get_body_class() );
	}
}
