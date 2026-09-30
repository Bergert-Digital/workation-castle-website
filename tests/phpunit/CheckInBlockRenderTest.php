<?php

class CheckInBlockRenderTest extends WP_UnitTestCase {

	public function test_block_renders_root_config_and_noscript() {
		$html = do_blocks( '<!-- wp:workation/check-in-form /-->' );
		$this->assertStringContainsString( 'wc-checkin', $html );
		$this->assertStringContainsString( 'wc-checkin-config', $html );
		$this->assertStringContainsString( 'workation/v1/check-in', $html );
		$this->assertStringContainsString( '<noscript', $html );
		// Config JSON includes the caps from CheckIn::config().
		$this->assertStringContainsString( '"maxGuests":20', $html );
	}

	public function test_block_emits_no_nonce() {
		// A page cache serves this markup for longer than a nonce lives, so a
		// baked-in nonce turns into a 403 on submit.
		$html = do_blocks( '<!-- wp:workation/check-in-form /-->' );
		$this->assertStringNotContainsString( '"nonce"', $html );
	}

	public function test_config_includes_draft_resume_strings() {
		$config = \Workation\CheckIn::config();
		$this->assertArrayHasKey( 'restoredNotice', $config['strings'] );
		$this->assertArrayHasKey( 'startOver', $config['strings'] );
		$this->assertNotEmpty( $config['strings']['restoredNotice'] );
		$this->assertNotEmpty( $config['strings']['startOver'] );
	}
}
