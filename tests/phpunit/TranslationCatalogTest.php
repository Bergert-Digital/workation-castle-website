<?php

/**
 * Guards the translation catalogs the way NavTranslationsTest guards nav labels:
 * adding an untranslated string fails the build instead of shipping English.
 */
class TranslationCatalogTest extends WP_UnitTestCase {

	private const LOCALES = array( 'de_DE', 'fr_FR', 'it_IT', 'nl_NL' );

	private function languages_dir(): string {
		return get_stylesheet_directory() . '/languages';
	}

	/** msgid => msgstr for a .po file, including untranslated entries. */
	private function parse_po( string $path ): array {
		$entries = array();
		$id      = null;
		$str     = null;
		$target  = null;

		foreach ( file( $path, FILE_IGNORE_NEW_LINES ) as $line ) {
			$line = trim( $line );

			if ( '' === $line || 0 === strpos( $line, '#' ) ) {
				if ( null !== $id ) {
					$entries[ $id ] = (string) $str;
					$id             = null;
					$str            = null;
					$target         = null;
				}
				continue;
			}
			if ( 0 === strpos( $line, 'msgid ' ) ) {
				if ( null !== $id ) {
					$entries[ $id ] = (string) $str;
				}
				$id     = $this->unquote( $line );
				$str    = null;
				$target = 'id';
				continue;
			}
			if ( 0 === strpos( $line, 'msgstr' ) ) {
				$str    = $this->unquote( $line );
				$target = 'str';
				continue;
			}
			if ( 0 === strpos( $line, '"' ) ) {
				if ( 'id' === $target ) {
					$id .= $this->unquote( $line );
				} elseif ( 'str' === $target ) {
					$str .= $this->unquote( $line );
				}
			}
		}
		if ( null !== $id ) {
			$entries[ $id ] = (string) $str;
		}

		unset( $entries[''] ); // The header is not a translatable string.
		return $entries;
	}

	private function unquote( string $line ): string {
		$start = strpos( $line, '"' );
		$raw   = substr( $line, $start + 1, strrpos( $line, '"' ) - $start - 1 );
		return str_replace(
			array( '\\n', '\\t', '\\"', '\\\\' ),
			array( "\n", "\t", '"', '\\' ),
			$raw
		);
	}

	/** printf placeholders in a string, normalised for comparison. */
	private function placeholders( string $text ): array {
		preg_match_all( '/%(?:\d+\$)?[sd]/', $text, $matches );
		$found = $matches[0];
		sort( $found );
		return $found;
	}

	public function test_the_template_exists_and_is_not_empty() {
		$pot = $this->languages_dir() . '/pediment-child.pot';
		$this->assertFileExists( $pot, 'Run `npm run i18n:pot`.' );
		$this->assertGreaterThan(
			50,
			count( $this->parse_po( $pot ) ),
			'The template extracted almost nothing — check the make-pot exclude list.'
		);
	}

	/** @dataProvider locales */
	public function test_every_template_string_is_translated( string $locale ) {
		$template = $this->parse_po( $this->languages_dir() . '/pediment-child.pot' );
		$po       = $this->languages_dir() . "/{$locale}.po";
		$this->assertFileExists( $po );
		$catalog = $this->parse_po( $po );

		$missing = array();
		foreach ( array_keys( $template ) as $msgid ) {
			if ( ! isset( $catalog[ $msgid ] ) || '' === $catalog[ $msgid ] ) {
				$missing[] = $msgid;
			}
		}

		$this->assertSame(
			array(),
			$missing,
			"{$locale} is missing translations for:\n  " . implode( "\n  ", $missing )
		);
	}

	/** @dataProvider locales */
	public function test_placeholders_survive_translation( string $locale ) {
		$catalog = $this->parse_po( $this->languages_dir() . "/{$locale}.po" );

		foreach ( $catalog as $msgid => $msgstr ) {
			if ( '' === $msgstr ) {
				continue;
			}
			$this->assertSame(
				$this->placeholders( $msgid ),
				$this->placeholders( $msgstr ),
				"{$locale}: placeholders differ between\n  '{$msgid}'\n  '{$msgstr}'"
			);
		}
	}

	/** @dataProvider locales */
	public function test_no_entry_is_fuzzy( string $locale ) {
		$contents = file_get_contents( $this->languages_dir() . "/{$locale}.po" );

		$this->assertStringNotContainsString(
			'#, fuzzy',
			$contents,
			"{$locale} has fuzzy entries — a fuzzy translation is dropped at compile time and renders English."
		);
	}

	/** @dataProvider locales */
	public function test_the_compiled_mo_matches_the_po( string $locale ) {
		$mo_path = $this->languages_dir() . "/{$locale}.mo";
		$this->assertFileExists( $mo_path, 'Run `npm run i18n:mo` and commit the result.' );

		$mo = new MO();
		$this->assertTrue( $mo->import_from_file( $mo_path ), "{$locale}.mo is not readable." );

		$po = $this->parse_po( $this->languages_dir() . "/{$locale}.po" );

		foreach ( $po as $msgid => $msgstr ) {
			if ( '' === $msgstr ) {
				continue;
			}
			$this->assertArrayHasKey(
				$msgid,
				$mo->entries,
				"{$locale}.mo is stale — '{$msgid}' is in the .po but not the .mo. Run `npm run i18n:mo`."
			);
			$this->assertSame(
				$msgstr,
				$mo->entries[ $msgid ]->translations[0],
				"{$locale}.mo is stale for '{$msgid}'. Run `npm run i18n:mo`."
			);
		}
	}

	public function locales(): array {
		return array_map(
			function ( $locale ) {
				return array( $locale );
			},
			self::LOCALES
		);
	}
}
