<?php

/**
 * Guards the Workation Castle child-theme reskin.
 *
 * The child theme intentionally forks the parent's palette and typography
 * subtrees. These tests make sure we keep the full Pediment slug surface
 * while applying the Workation Castle brand.
 */
class ThemeJsonInheritsPedimentTest extends WP_UnitTestCase {

	/**
	 * Clean the resolver caches and return the freshly merged
	 * (parent ⊕ child) global settings.
	 *
	 * @return array<string,mixed>
	 */
	private function fresh_settings() {
		WP_Theme_JSON_Resolver::clean_cached_data();
		return WP_Theme_JSON_Resolver::get_merged_data()->get_settings();
	}

	private function assert_child_theme_active() {
		$this->assertContains(
			get_stylesheet(),
			array( 'pediment-child-theme', 'accra' ),
			'These theme-json guards are only meaningful with this child theme active.'
		);
	}

	/**
	 * @return array<string,string> slug => hex, from the theme-origin palette.
	 */
	private function theme_palette() {
		$settings = $this->fresh_settings();
		$this->assert_child_theme_active();
		$palette = isset( $settings['color']['palette']['theme'] )
			? $settings['color']['palette']['theme']
			: array();
		$by_slug = array();
		foreach ( $palette as $entry ) {
			if ( isset( $entry['slug'], $entry['color'] ) ) {
				$by_slug[ $entry['slug'] ] = $entry['color'];
			}
		}
		return $by_slug;
	}

	public function test_workation_castle_palette_preserves_required_slugs() {
		$by_slug = $this->theme_palette();
		$this->assertSame(
			array(
				'primary',
				'accent',
				'accent-hover',
				'accent-tint',
				'surface',
				'surface-elevated',
				'surface-sunken',
				'foreground',
				'foreground-muted',
				'border',
				'border-strong',
			),
			array_keys( $by_slug ),
			'The child palette must keep the full Pediment slug surface.'
		);
	}

	public function test_workation_castle_palette_applies_brand_colors() {
		$by_slug = $this->theme_palette();
		$this->assertSame( '#FEC601', $by_slug['accent'] );
		$this->assertSame( '#E5A800', $by_slug['accent-hover'] );
		$this->assertSame( '#FAF6EE', $by_slug['surface'] );
		$this->assertSame( '#F2EADA', $by_slug['surface-sunken'] );
		$this->assertSame( '#241C12', $by_slug['foreground'] );
	}

	public function test_workation_castle_typography_uses_inria_families() {
		$settings = $this->fresh_settings();
		$this->assert_child_theme_active();
		$families = isset( $settings['typography']['fontFamilies']['theme'] )
			? $settings['typography']['fontFamilies']['theme']
			: array();
		$by_slug = array();
		foreach ( $families as $family ) {
			if ( isset( $family['slug'], $family['fontFamily'] ) ) {
				$by_slug[ $family['slug'] ] = $family['fontFamily'];
			}
		}

		$this->assertStringContainsString( 'Inria Sans', $by_slug['body'] );
		$this->assertStringContainsString( 'Inria Serif', $by_slug['heading'] );
		$this->assertArrayHasKey( 'mono', $by_slug );
	}

	public function test_child_theme_json_declares_intentional_settings_override() {
		$this->assert_child_theme_active();
		$path = get_stylesheet_directory() . '/theme.json';
		$this->assertFileIsReadable( $path );
		$data = json_decode( file_get_contents( $path ), true );
		$this->assertIsArray( $data );
		$this->assertSame( 2, $data['version'] );
		$this->assertArrayHasKey( 'settings', $data );
		$this->assertArrayHasKey( 'color', $data['settings'] );
		$this->assertArrayHasKey( 'typography', $data['settings'] );
	}
}
