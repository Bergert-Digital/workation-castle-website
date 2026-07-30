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

	public function tear_down() {
		restore_previous_locale();
		parent::tear_down();
	}

	public function test_modal_strings_are_localized() {
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );

		$data = wp_scripts()->get_data( 'workation-castle-consent', 'data' );
		$json = json_decode( substr( $data, strpos( $data, '{' ), strrpos( $data, '}' ) - strpos( $data, '{' ) + 1 ), true );

		$this->assertArrayHasKey( 'strings', $json );
		$this->assertSame( 'Your privacy', $json['strings']['title'] );
		$this->assertSame( 'Accept all', $json['strings']['acceptAll'] );
		$this->assertSame( 'Necessary', $json['strings']['categories']['necessary']['label'] );
		$this->assertSame(
			'Personalised content and ad measurement.',
			$json['strings']['categories']['marketing']['desc']
		);
	}

	public function test_modal_strings_follow_the_locale() {
		switch_to_locale( 'de_DE' );
		$this->go_to( home_url( '/' ) );
		do_action( 'wp_enqueue_scripts' );

		$data = wp_scripts()->get_data( 'workation-castle-consent', 'data' );
		$this->assertStringContainsString( 'Deine Privatsph', $data );
	}
}
