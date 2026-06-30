<?php

class ConsentEnqueueTest extends WP_UnitTestCase {

	public function test_consent_assets_are_enqueued_on_front_end() {
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'workation-castle-consent', 'enqueued' ), 'consent CSS enqueued' );
		$this->assertTrue( wp_script_is( 'workation-castle-consent', 'enqueued' ), 'consent JS enqueued' );
	}

	public function test_config_is_localized() {
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );

		$data = wp_scripts()->get_data( 'workation-castle-consent', 'data' );
		$this->assertIsString( $data );
		$this->assertStringContainsString( 'wcConsentConfig', $data );
		$this->assertStringContainsString( '"cookieName":"wc_consent"', $data );
		$this->assertStringContainsString( '"version":1', $data );
	}
}
