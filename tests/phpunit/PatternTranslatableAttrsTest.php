<?php

/**
 * Polylang/DeepL can only translate block attributes that are serialized into
 * post_content. An attribute left to fall back to its block.json default is not
 * in the page at all, so it is invisible to translation and every language
 * re-reads the same English default from the theme.
 */
class PatternTranslatableAttrsTest extends WP_UnitTestCase {

	/** Serve a fixture for any sideload HTTP request (no network). */
	public function setUp(): void {
		parent::setUp();
		add_filter( 'pre_http_request', array( $this, 'serve_fixture' ), 10, 3 );
	}

	public function serve_fixture( $pre, $args, $url ) {
		$body = file_get_contents( __DIR__ . '/fixtures/sample-photo.jpg' );
		if ( ! empty( $args['filename'] ) ) {
			file_put_contents( $args['filename'], $body );
		}
		return array(
			'headers'  => array(
				'content-type'        => 'image/jpeg',
				'content-disposition' => 'inline; filename=sample-photo.jpg',
			),
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'filename' => ! empty( $args['filename'] ) ? $args['filename'] : null,
			'body'     => '',
		);
	}

	/** Theme root. */
	private function theme_dir() {
		return dirname( __DIR__, 2 );
	}

	/** Block name => translatable text keys, per the theme's wpml-config.xml. */
	private function translatable_keys() {
		$xml  = simplexml_load_file( $this->theme_dir() . '/wpml-config.xml' );
		$keys = array();
		foreach ( $xml->xpath( 'gutenberg-blocks/gutenberg-block' ) as $block ) {
			$names = array();
			foreach ( $block->key as $key ) {
				// type="link" attributes are URLs: remapped, never translated.
				if ( 'link' !== (string) $key['type'] ) {
					$names[] = (string) $key['name'];
				}
			}
			$keys[ (string) $block['type'] ] = $names;
		}
		return $keys;
	}

	/** Block name => [ attribute => default ], per each block.json. */
	private function block_defaults() {
		$defaults = array();
		foreach ( glob( $this->theme_dir() . '/src/blocks/*/block.json' ) as $file ) {
			$json = json_decode( (string) file_get_contents( $file ), true );
			foreach ( (array) ( $json['attributes'] ?? array() ) as $name => $schema ) {
				$defaults[ $json['name'] ][ $name ] = $schema['default'] ?? null;
			}
		}
		return $defaults;
	}

	/** Flatten a parse_blocks() tree. */
	private function flatten( array $blocks ) {
		$flat = array();
		foreach ( $blocks as $block ) {
			$flat[] = $block;
			if ( ! empty( $block['innerBlocks'] ) ) {
				$flat = array_merge( $flat, $this->flatten( $block['innerBlocks'] ) );
			}
		}
		return $flat;
	}

	/**
	 * Every declared translatable attribute with a non-empty default must be
	 * written into the pattern markup rather than left to the default.
	 */
	public function test_no_translatable_attribute_falls_back_to_a_default() {
		$translatable = $this->translatable_keys();
		$defaults     = $this->block_defaults();
		$offenders    = array();

		foreach ( \PedimentChild\Seed::PAGES as $slug => $info ) {
			ob_start();
			include $this->theme_dir() . '/' . $info['pattern_file'];
			$markup = (string) ob_get_clean();

			foreach ( $this->flatten( parse_blocks( $markup ) ) as $block ) {
				$name = (string) $block['blockName'];
				if ( ! isset( $translatable[ $name ], $defaults[ $name ] ) ) {
					continue;
				}
				foreach ( $translatable[ $name ] as $key ) {
					$default = $defaults[ $name ][ $key ] ?? null;
					if ( ! is_string( $default ) || '' === trim( $default ) ) {
						continue;
					}
					if ( ! array_key_exists( $key, (array) $block['attrs'] ) ) {
						$offenders[] = "{$info['pattern_file']}: {$name}.{$key}";
					}
				}
			}
		}

		$this->assertSame(
			array(),
			$offenders,
			"These attributes fall back to a block.json default, so Polylang cannot translate them:\n"
				. implode( "\n", $offenders )
		);
	}

	/**
	 * The hero headline carries a <span class="hl"> highlight, which core
	 * serializes as backslash-u escapes. wp_insert_post() unslashes what it is
	 * given, so the seed has to slash the content or the escapes are eaten and
	 * the stored headline reads "u003cspan".
	 */
	public function test_seeded_headline_survives_the_database_round_trip() {
		\PedimentChild\Seed::seed();

		$page = get_page_by_path( 'home' );
		$this->assertInstanceOf( \WP_Post::class, $page );

		// The escapes must still carry their backslashes. Unslashed content
		// would read `u003cspan` here instead. Single-quoted on purpose: the
		// backslashes are literal.
		$this->assertStringContainsString(
			'\u003cspan class=\u0022hl\u0022\u003e',
			$page->post_content
		);

		$hero = null;
		foreach ( $this->flatten( parse_blocks( $page->post_content ) ) as $block ) {
			if ( 'pediment-child/workation-hero' === $block['blockName'] ) {
				$hero = $block;
				break;
			}
		}

		$this->assertNotNull( $hero, 'The seeded home page has no workation-hero block.' );

		$defaults = $this->block_defaults();
		$this->assertSame(
			$defaults['pediment-child/workation-hero']['headline'],
			$hero['attrs']['headline'],
			'The stored headline no longer matches the block default byte for byte.'
		);
		$this->assertStringContainsString( '<span class="hl">', $hero['attrs']['headline'] );
	}
}
