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
}
