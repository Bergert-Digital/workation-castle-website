<?php
// tests/phpunit/CheckInPatternTest.php

/**
 * The check-in page's content lives in patterns/check-in.php, which the seed
 * manifest names as the `check-in` entry's pattern.
 */
class CheckInPatternTest extends PatternTestCase {

	public function test_the_pattern_declares_the_check_in_slug() {
		$source = file_get_contents( $this->theme_dir() . '/patterns/check-in.php' );

		$this->assertStringContainsString( 'Slug: workation/check-in', $source );
	}

	public function test_the_pattern_carries_the_check_in_form_block() {
		$markup = $this->render_pattern( 'check-in.php' );

		$this->assertStringContainsString( 'wp:workation/check-in-form', $markup );
	}
}
