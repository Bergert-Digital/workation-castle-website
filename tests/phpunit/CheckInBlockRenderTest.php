<?php

class CheckInBlockRenderTest extends WP_UnitTestCase {

	public function test_block_renders_root_config_and_noscript() {
		$html = do_blocks( '<!-- wp:pediment-child/check-in-form /-->' );
		$this->assertStringContainsString( 'wc-checkin', $html );
		$this->assertStringContainsString( 'wc-checkin-config', $html );
		$this->assertStringContainsString( 'pediment-child/v1/check-in', $html );
		$this->assertStringContainsString( '<noscript', $html );
		// Config JSON includes the caps from CheckIn::config().
		$this->assertStringContainsString( '"maxGuests":20', $html );
	}
}
