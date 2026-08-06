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

		$patterns = glob( $this->theme_dir() . '/patterns/*.php' );
		$this->assertNotEmpty( $patterns, 'No pattern files found — this test would pass vacuously.' );

		foreach ( $patterns as $file ) {
			$relative = 'patterns/' . basename( $file );

			ob_start();
			include $file;
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
						$offenders[] = "{$relative}: {$name}.{$key}";
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
	 * serializes as backslash-u escapes inside the block comment's JSON. The
	 * pattern file has to carry those escapes literally: unescaped markup makes
	 * the attribute JSON unparseable, and an unslashed copy stores "u003cspan".
	 */
	public function test_the_home_hero_headline_keeps_its_escaped_highlight() {
		ob_start();
		include $this->theme_dir() . '/patterns/home.php';
		$markup = (string) ob_get_clean();

		// The escapes must still carry their backslashes. Unescaped markup
		// would read `<span` here instead. Single-quoted on purpose: the
		// backslashes are literal.
		$this->assertStringContainsString(
			'\u003cspan class=\u0022hl\u0022\u003e',
			$markup
		);

		$hero = null;
		foreach ( $this->flatten( parse_blocks( $markup ) ) as $block ) {
			if ( 'pediment-child/workation-hero' === $block['blockName'] ) {
				$hero = $block;
				break;
			}
		}

		$this->assertNotNull( $hero, 'The home pattern has no workation-hero block.' );

		// The headline no longer has a block.json default; the legacy copy map
		// holds the same English string, so it doubles as the reference here
		// and keeps the map and the patterns from drifting apart.
		$legacy = pediment_child_legacy_block_copy();
		$this->assertSame(
			$legacy['pediment-child/workation-hero']['headline'],
			$hero['attrs']['headline'],
			'The pattern headline and the legacy copy map have drifted apart.'
		);
		$this->assertStringContainsString( '<span class="hl">', $hero['attrs']['headline'] );
	}

	/**
	 * Gutenberg's getCommentAttributes() omits any attribute whose value equals
	 * its default when it serializes a block, so a translatable attribute that
	 * carries a default is stripped out of post_content the moment someone
	 * saves the page in the editor -- undoing the fix above. Keeping these
	 * attributes default-free is what makes that impossible.
	 */
	public function test_no_translatable_attribute_carries_a_block_json_default() {
		$translatable = $this->translatable_keys();
		$offenders    = array();

		foreach ( $this->block_defaults() as $block => $attributes ) {
			if ( ! isset( $translatable[ $block ] ) ) {
				continue;
			}
			foreach ( $translatable[ $block ] as $key ) {
				$default = $attributes[ $key ] ?? null;
				if ( is_string( $default ) && '' !== trim( $default ) ) {
					$offenders[] = "{$block}.{$key}";
				}
			}
		}

		$this->assertSame(
			array(),
			$offenders,
			"These translatable attributes still have a block.json default, so an\n"
				. "editor save will strip them back out of post_content:\n"
				. implode( "\n", $offenders )
		);
	}

	/**
	 * Dropping the defaults would otherwise blank the hero, section headers and
	 * closing CTA on any page seeded before the copy moved into the patterns.
	 */
	public function test_legacy_content_without_attributes_still_renders_its_copy() {
		$rendered = do_blocks(
			'<!-- wp:pediment-child/workation-spaces --><!-- /wp:pediment-child/workation-spaces -->'
		);

		$this->assertStringContainsString( 'The spaces', $rendered );
		$this->assertStringContainsString( 'Room to work, and room to stay.', $rendered );
	}

	/** An attribute emptied on purpose hides its element and is not refilled. */
	public function test_an_emptied_attribute_is_not_refilled() {
		$rendered = do_blocks(
			'<!-- wp:pediment-child/workation-spaces {"headline":""} --><!-- /wp:pediment-child/workation-spaces -->'
		);

		$this->assertStringNotContainsString( 'Room to work, and room to stay.', $rendered );
	}
}
