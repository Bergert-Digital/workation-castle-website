<?php

class PrimaryNavRenderTest extends WP_UnitTestCase {

	private function make_primary_menu(): int {
		return wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_title'   => 'Primary',
				'post_name'    => 'primary',
				'post_status'  => 'publish',
				'post_content' => pediment_child_primary_nav_blocks(),
			)
		);
	}

	private function nav_block( array $attrs = array(), array $inner = array(), array $inner_content = array() ): array {
		return array(
			'blockName'    => 'core/navigation',
			'attrs'        => $attrs,
			'innerBlocks'  => $inner,
			'innerHTML'    => '',
			'innerContent' => $inner_content,
		);
	}

	public function test_injects_primary_menu_ref_onto_refless_navigation() {
		$id  = $this->make_primary_menu();
		$out = pediment_child_inject_primary_nav_ref( $this->nav_block() );
		$this->assertSame( $id, $out['attrs']['ref'] );
	}

	public function test_leaves_explicitly_referenced_navigation_untouched() {
		$this->make_primary_menu();
		$out = pediment_child_inject_primary_nav_ref( $this->nav_block( array( 'ref' => 999 ) ) );
		$this->assertSame( 999, $out['attrs']['ref'] );
	}

	public function test_leaves_navigation_unchanged_when_no_primary_menu() {
		$inner         = array( array( 'blockName' => 'core/page-list' ) );
		$inner_content = array( 'x' );
		$out           = pediment_child_inject_primary_nav_ref(
			$this->nav_block( array(), $inner, $inner_content )
		);
		$this->assertArrayNotHasKey( 'ref', $out['attrs'] );
		$this->assertSame( $inner, $out['innerBlocks'] );
		$this->assertSame( $inner_content, $out['innerContent'] );
	}

	public function test_ignores_non_navigation_blocks() {
		$block = array( 'blockName' => 'core/paragraph', 'attrs' => array(), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() );
		$this->assertSame( $block, pediment_child_inject_primary_nav_ref( $block ) );
	}

	public function test_absent_primary_menu_renders_nothing_and_creates_no_stray_menu() {
		// No primary menu exists.
		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );
		$this->assertStringNotContainsString( 'wp-block-page-list', $html );
		$this->assertSame( '', trim( $html ) );
		$stray = get_posts( array( 'post_type' => 'wp_navigation', 'post_status' => 'any', 'numberposts' => -1 ) );
		$this->assertCount( 0, $stray, 'No stray wp_navigation should be auto-created' );
	}

	public function test_seeded_primary_menu_renders_expected_links() {
		$this->make_primary_menu();
		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );
		$this->assertNotSame( '', trim( $html ) );
		$this->assertStringContainsString( 'Activities', $html );
	}

	/**
	 * Hide the menu from filtered queries only, the way a multilingual plugin
	 * scopes results to the language being rendered. WP_Query skips these
	 * filters when suppress_filters is true, so this leaves the unfiltered
	 * fallback query intact — exactly the asymmetry the lookup relies on.
	 *
	 * @return callable The filter, so the caller can remove it.
	 */
	private function hide_from_filtered_queries(): callable {
		$filter = static function ( $where ) {
			return $where . ' AND 1=0';
		};
		add_filter( 'posts_where', $filter );
		return $filter;
	}

	public function test_menu_is_found_even_when_a_language_filter_hides_it() {
		$id     = $this->make_primary_menu();
		$filter = $this->hide_from_filtered_queries();

		$menu = pediment_child_get_primary_nav_menu();

		remove_filter( 'posts_where', $filter );
		$this->assertNotNull( $menu, 'A language-scoped query must not cost the site its navigation' );
		$this->assertSame( $id, $menu->ID );
	}

	public function test_navigation_still_renders_when_a_language_filter_hides_the_menu() {
		$this->make_primary_menu();
		$filter = $this->hide_from_filtered_queries();

		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );

		remove_filter( 'posts_where', $filter );
		$this->assertStringContainsString( 'Activities', $html, 'The header must keep its links' );
	}

	/**
	 * Hide the menu the way Polylang actually does.
	 *
	 * Polylang never reads `suppress_filters`. It hooks `parse_query` and pushes
	 * a language clause into `query_vars['tax_query']`, which WP_Query re-parses
	 * in get_posts() for every non-singular query — so a `suppress_filters`
	 * retry alone comes back just as empty as the first attempt. The one thing
	 * it does respect is the `lang` query var: PLL_Query::is_already_filtered()
	 * returns true on a bare `isset()`, so an empty value opts the query out.
	 * This mirrors both halves, and only those.
	 *
	 * @param string $taxonomy Stand-in for Polylang's `language` taxonomy.
	 * @return callable The action, so the caller can remove it.
	 */
	private function hide_the_way_polylang_does( string $taxonomy ): callable {
		$action = static function ( $query ) use ( $taxonomy ) {
			$qv = &$query->query_vars;
			if ( 'wp_navigation' !== ( isset( $qv['post_type'] ) ? $qv['post_type'] : '' ) ) {
				return;
			}
			if ( isset( $qv['lang'] ) ) {
				return; // PLL_Query::is_already_filtered().
			}
			$clause = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => array( 'xx' ),
				'operator' => 'IN',
			);
			if ( empty( $qv['tax_query'] ) ) {
				$qv['tax_query'] = array( $clause );
			} elseif ( is_array( $qv['tax_query'] ) ) {
				$qv['tax_query'][] = $clause;
			}
		};
		add_action( 'parse_query', $action );
		return $action;
	}

	/**
	 * The regression that made this branch's wp_navigation translatability
	 * dangerous: an existing site's menu carries no language term, so Polylang's
	 * language clause hides it in *every* language, and a menu whose slug is not
	 * literally `primary` then has no fallback left — the header renders no
	 * navigation at all, sitewide.
	 */
	public function test_menu_survives_polylang_style_tax_query_scoping() {
		$taxonomy = 'pediment_test_language';
		register_taxonomy( $taxonomy, 'wp_navigation', array( 'public' => false ) );
		wp_insert_term( 'xx', $taxonomy );

		// Deliberately not slug `primary`: the legacy slug fallback must not be
		// what rescues this, or the test would pass with the bug present.
		$id = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_title'   => 'Primary',
				'post_name'    => 'primary-2',
				'post_status'  => 'publish',
				'post_content' => pediment_child_primary_nav_blocks(),
			)
		);
		update_post_meta( $id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );

		$args   = array(
			'post_status' => 'publish',
			'meta_key'    => PEDIMENT_CHILD_PRIMARY_NAV_MARKER,
		);
		$action = $this->hide_the_way_polylang_does( $taxonomy );

		// The simulation has to actually bite, or the assertion below proves
		// nothing.
		$scoped = get_posts(
			array_merge(
				$args,
				array(
					'post_type'        => 'wp_navigation',
					'numberposts'      => 1,
					'suppress_filters' => true,
				)
			)
		);
		$menu   = pediment_child_find_nav_post( $args );

		remove_action( 'parse_query', $action );
		unregister_taxonomy( $taxonomy );

		$this->assertSame( array(), $scoped, 'suppress_filters alone must not defeat a tax_query language clause — otherwise this test is not reproducing Polylang' );
		$this->assertNotNull( $menu, 'A Polylang-style language clause must not cost the site its navigation' );
		$this->assertSame( $id, $menu->ID );
	}

	/**
	 * The slug is no longer what identifies the menu, so a post squatting
	 * `primary` cannot hide the real one. WordPress keeps slugs unique, so a
	 * squatter used to force every replacement to `primary-2` — invisible to a
	 * slug lookup, and the site rendered no navigation at all.
	 */
	public function test_marked_menu_wins_over_a_post_squatting_the_slug() {
		$squatter = wp_insert_post(
			array(
				'post_type'    => 'wp_navigation',
				'post_title'   => 'Squatter',
				'post_name'    => 'primary',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:navigation-link {"label":"Wrong","url":"/wrong/"} /-->',
			)
		);
		// Inserted second, so WordPress hands it the suffixed slug.
		$real = $this->make_primary_menu();
		update_post_meta( $real, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, '1' );

		$menu = pediment_child_get_primary_nav_menu();

		$this->assertNotNull( $menu );
		$this->assertSame( $real, $menu->ID, 'The marked menu must win regardless of its slug' );
		$this->assertNotSame( $squatter, $menu->ID );
	}

	/**
	 * Sites seeded before the marker existed only have the `primary` slug. They
	 * must keep working, and get stamped so later lookups no longer depend on
	 * the slug at all.
	 */
	public function test_legacy_slug_menu_is_still_found_and_gets_stamped() {
		$id = $this->make_primary_menu();
		delete_post_meta( $id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER );

		$menu = pediment_child_get_primary_nav_menu();

		$this->assertNotNull( $menu, 'A pre-marker site must keep its navigation' );
		$this->assertSame( $id, $menu->ID );
		$this->assertSame( '1', get_post_meta( $id, PEDIMENT_CHILD_PRIMARY_NAV_MARKER, true ), 'Lookup must stamp the marker so the slug stops mattering' );
	}

	public function test_absent_menu_still_renders_nothing_when_filters_are_bypassed() {
		// The fallback must not resurrect the page-list fallback on a fresh site.
		$html = do_blocks( '<!-- wp:navigation {"overlayMenu":"mobile"} /-->' );
		$this->assertSame( '', trim( $html ) );
		$this->assertNull( pediment_child_get_primary_nav_menu() );
	}
}
