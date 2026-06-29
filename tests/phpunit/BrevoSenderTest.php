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
			'ids'    => array( array( 'doc_type' => 'passport', 'doc_number' => 'X1' ) ),
			'counts' => array( 'guests' => 1, 'houses' => 1 ),
		);
	}

	public function tearDown(): void {
		putenv( 'BREVO_API_KEY' );
		remove_all_filters( 'pre_http_request' );
		parent::tearDown();
	}

	public function test_skips_when_no_api_key() {
		putenv( 'BREVO_API_KEY' ); // unset
		$result = \PedimentChild\Brevo::send_checkin_notification( $this->sample() );
		$this->assertSame( 'skipped', $result['status'] );
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
		$result = \PedimentChild\Brevo::send_checkin_notification( $this->sample() );
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
		$result = \PedimentChild\Brevo::send_checkin_notification( $this->sample() );
		$this->assertSame( 'failed', $result['status'] );
	}
}
