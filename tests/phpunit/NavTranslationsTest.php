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
}
