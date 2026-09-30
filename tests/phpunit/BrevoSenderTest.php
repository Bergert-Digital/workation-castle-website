<?php
// tests/phpunit/BrevoSenderTest.php

class BrevoSenderTest extends WP_UnitTestCase {

	private function sample(): array {
		return array(
			'guests' => array(
				array(
					'first_name'     => 'Jane',
					'last_name'      => 'Doe',
					'nationality'    => 'British',
					'residence_city' => 'London',
					'birthdate'      => '1990-05-01',
					'birth_city'     => 'Leeds',
					'gender'         => 'female',
				),
			),
			'ids'    => array( array( 'guest_index' => 0, 'doc_type' => 'passport', 'doc_number' => 'X1' ) ),
			'counts' => array( 'guests' => 1, 'houses' => 1 ),
		);
	}

	public function setUp(): void {
		parent::setUp();
		// The harness boots the theme without the Pediment plugin, so load its
		// secret store (Form submissions → Settings → Secrets) directly.
		if ( ! function_exists( 'pediment_form_secret_get' ) ) {
			require_once WP_PLUGIN_DIR . '/pediment-plugin/inc/forms-secrets.php';
		}
	}

	public function tearDown(): void {
		putenv( 'BREVO_API_KEY' );
		delete_option( PEDIMENT_FORM_SECRETS_OPTION );
		remove_all_filters( 'pre_http_request' );
		parent::tearDown();
	}

	public function test_skips_when_no_api_key() {
		putenv( 'BREVO_API_KEY' ); // unset
		$result = \Workation\Brevo::send_checkin_notification( $this->sample() );
		$this->assertSame( 'skipped', $result['status'] );
	}

	public function test_reads_key_from_pediment_form_secret() {
		putenv( 'BREVO_API_KEY' ); // unset
		pediment_form_secret_set( 'brevo_api_key', 'secret-key' );
		$this->assertSame( 'secret-key', \Workation\Brevo::api_key() );
	}

	public function test_form_secret_wins_over_environment() {
		putenv( 'BREVO_API_KEY=env-key' );
		pediment_form_secret_set( 'brevo_api_key', 'secret-key' );
		$this->assertSame( 'secret-key', \Workation\Brevo::api_key() );
	}

	public function test_sent_on_2xx() {
		putenv( 'BREVO_API_KEY=test-key' );
		add_filter(
			'pre_http_request',
			function () {
				return array(
					'response' => array( 'code' => 201, 'message' => 'Created' ),
					'body'     => '{"messageId":"abc"}',
				);
			}
		);
		$result = \Workation\Brevo::send_checkin_notification( $this->sample() );
		$this->assertSame( 'sent', $result['status'] );
	}

	public function test_failed_on_error_response() {
		putenv( 'BREVO_API_KEY=test-key' );
		add_filter(
			'pre_http_request',
			function () {
				return array(
					'response' => array( 'code' => 400, 'message' => 'Bad Request' ),
					'body'     => '{"message":"bad"}',
				);
			}
		);
		$result = \Workation\Brevo::send_checkin_notification( $this->sample() );
		$this->assertSame( 'failed', $result['status'] );
	}
}
