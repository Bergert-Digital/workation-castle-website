<?php

class NavTranslationsTest extends WP_UnitTestCase {

	/** Every label in the canonical nav source, including nested submenu items. */
	private function nav_labels(): array {
		$labels  = array();
		$collect = function ( array $blocks ) use ( &$collect, &$labels ) {
			foreach ( $blocks as $block ) {
				if ( isset( $block['attrs']['label'] ) ) {
					$labels[] = $block['attrs']['label'];
				}
				if ( ! empty( $block['innerBlocks'] ) ) {
					$collect( $block['innerBlocks'] );
				}
			}
		};
		$collect( parse_blocks( pediment_child_primary_nav_blocks() ) );

		return $labels;
	}

	public function test_every_nav_label_has_a_translation_entry() {
		$labels = $this->nav_labels();
		$this->assertNotEmpty( $labels, 'The nav source yielded no labels — the collector is broken.' );

		foreach ( $labels as $label ) {
			$this->assertArrayHasKey(
				$label,
				PEDIMENT_CHILD_NAV_LABELS,
				"No translation entry for menu label '{$label}'."
			);
		}
	}

	public function test_every_entry_covers_the_same_languages() {
		$expected = array( 'de', 'fr', 'it', 'nl' );

		foreach ( PEDIMENT_CHILD_NAV_LABELS as $label => $translations ) {
			$actual = array_keys( $translations );
			sort( $actual );
			$this->assertSame( $expected, $actual, "Label '{$label}' does not cover every language." );
		}
	}

	public function test_labels_are_translated_and_structure_is_preserved() {
		$log    = array();
		$blocks = pediment_child_translate_nav_blocks(
			parse_blocks( pediment_child_primary_nav_blocks() ),
			'de',
			$log
		);

		$serialized = serialize_blocks( $blocks );
		$this->assertStringContainsString( 'Aktivitäten', $serialized );
		$this->assertStringContainsString( 'Aufenthaltsarten', $serialized );
		$this->assertStringNotContainsString( '"label":"Activities"', $serialized );

		// Structure is untouched: same block names in the same order.
		$original = parse_blocks( pediment_child_primary_nav_blocks() );
		$this->assertSame(
			wp_list_pluck( $original, 'blockName' ),
			wp_list_pluck( $blocks, 'blockName' )
		);
	}

	public function test_nested_submenu_items_are_translated() {
		$log    = array();
		$blocks = pediment_child_translate_nav_blocks(
			parse_blocks( pediment_child_primary_nav_blocks() ),
			'it',
			$log
		);

		// "How to get here" is a child of the "Guest Guide" submenu.
		$this->assertStringContainsString( 'Come arrivare', serialize_blocks( $blocks ) );
	}

	public function test_an_unknown_label_is_warned_about_and_left_alone() {
		$log    = array();
		$blocks = pediment_child_translate_nav_blocks(
			parse_blocks( '<!-- wp:navigation-link {"label":"Nonsense","url":"/nope/"} /-->' ),
			'de',
			$log
		);

		$this->assertSame( 'Nonsense', $blocks[0]['attrs']['label'] );
		$this->assertNotEmpty( array_filter( $log, function ( $line ) {
			return false !== strpos( $line, 'no de label' );
		} ) );
	}

	public function test_url_mapping_declines_without_polylang() {
		$this->assertNull( pediment_child_translate_nav_url( '/activities/', 'de' ) );
	}

	public function test_unmappable_urls_are_kept_and_warned_about() {
		$log    = array();
		$blocks = pediment_child_translate_nav_blocks(
			parse_blocks( '<!-- wp:navigation-link {"label":"FAQ","url":"/guide/faq/"} /-->' ),
			'de',
			$log
		);

		$this->assertSame( '/guide/faq/', $blocks[0]['attrs']['url'] );
		$this->assertNotEmpty( array_filter( $log, function ( $line ) {
			return false !== strpos( $line, 'cannot map de url' );
		} ) );
	}

	public function test_the_language_switcher_is_carried_into_translated_menus() {
		$log    = array();
		$blocks = pediment_child_translate_nav_blocks(
			parse_blocks( pediment_child_primary_nav_blocks() ),
			'de',
			$log
		);

		$this->assertStringContainsString(
			'wp:polylang/navigation-language-switcher',
			serialize_blocks( $blocks ),
			'Every language needs the switcher, or only the default language can be left.'
		);
		$this->assertEmpty(
			array_filter(
				$log,
				function ( $line ) {
					return false !== strpos( $line, 'no de label' );
				}
			),
			'The switcher carries no label, so it must not be reported as untranslated.'
		);
	}

	/** A wp_navigation post with the given content, standing in for a menu. */
	private function menu_with( string $content ): WP_Post {
		return self::factory()->post->create_and_get(
			array(
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_content' => $content,
			)
		);
	}

	public function test_a_language_with_no_menu_yet_gets_one_written() {
		$this->assertTrue( pediment_child_nav_translation_needs_content( null, false ) );
	}

	public function test_an_edited_translated_menu_is_left_alone() {
		$menu = $this->menu_with( '<!-- wp:navigation-link {"label":"Edited","url":"/x/"} /-->' );

		$this->assertFalse(
			pediment_child_nav_translation_needs_content( $menu, false ),
			'Re-seeding must not discard what a site owner edited in the Site Editor.'
		);
	}

	public function test_an_emptied_translated_menu_is_refilled() {
		$menu = $this->menu_with( '   ' );

		$this->assertTrue(
			pediment_child_nav_translation_needs_content( $menu, false ),
			'An empty menu leaves that language with no navigation and must be healed.'
		);
	}

	public function test_a_requested_rebuild_overwrites_an_edited_menu() {
		$menu = $this->menu_with( '<!-- wp:navigation-link {"label":"Edited","url":"/x/"} /-->' );

		$this->assertTrue(
			pediment_child_nav_translation_needs_content( $menu, true ),
			'Rebuilding is the explicit opt-in that regenerates menus from the theme source.'
		);
	}

	public function test_seeding_is_a_no_op_without_polylang() {
		$before = wp_count_posts( 'wp_navigation' );
		$log    = pediment_child_seed_nav_translations();
		$after  = wp_count_posts( 'wp_navigation' );

		$this->assertEquals( $before, $after, 'No navigation post may be written without Polylang.' );
		$this->assertNotEmpty( $log, 'The guard must explain itself rather than returning silence.' );
		$this->assertStringContainsString( 'Polylang', $log[0] );
	}
}
