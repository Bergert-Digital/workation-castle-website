<?php
/**
 * Shared base for tests that assert on a pattern file's markup.
 *
 * Pages are seeded by the Pediment plugin's manifest engine now, not by this
 * theme, so a test can no longer reach the page a pattern produced. The pattern
 * file itself is the theme-owned artefact, and it is what these tests read.
 *
 * @package Workation
 */

/**
 * Renders a theme pattern file to its block markup.
 */
abstract class PatternTestCase extends WP_UnitTestCase {

	/** Theme root. */
	protected function theme_dir(): string {
		return dirname( __DIR__, 2 );
	}

	/**
	 * Render a pattern file the way the pattern registry does — by including it
	 * and capturing its output, so any PHP in the file runs.
	 *
	 * @param string $file Pattern filename, relative to patterns/.
	 * @return string Block markup.
	 */
	protected function render_pattern( string $file ): string {
		$path = $this->theme_dir() . '/patterns/' . $file;
		$this->assertFileExists( $path );

		ob_start();
		include $path;
		return (string) ob_get_clean();
	}
}
