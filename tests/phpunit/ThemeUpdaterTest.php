<?php
/**
 * Tests the GitHub-release theme auto-updater wiring.
 *
 * These guard the two values whose drift silently breaks one-click updates:
 * the release-asset name and the repo the updater installs from. A packaging
 * change that renames the zip (as happened when it went from
 * workation-castle-theme.zip to workation.zip) must fail here, not in the field.
 *
 * @package Workation
 */

class ThemeUpdaterTest extends WP_UnitTestCase {

	public function test_asset_pattern_matches_the_released_zip_name() {
		$pattern = \Workation\ThemeUpdater::asset_pattern();

		$this->assertSame( 1, preg_match( $pattern, 'workation.zip' ) );
		// The GitHub "Source code" zip must never match: installing it strips
		// build/ and vendor/, unregistering every block.
		$this->assertSame( 0, preg_match( $pattern, 'workation-castle-website-1.1.0.zip' ) );
	}

	public function test_updates_come_from_this_repo() {
		$this->assertSame(
			'https://github.com/Bergert-Digital/workation-castle-website/',
			\Workation\ThemeUpdater::repo_url()
		);
	}
}
