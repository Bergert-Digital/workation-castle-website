<?php

class BrevoPayloadTest extends WP_UnitTestCase {

	private function sample(): array {
		return array(
			'guests'       => array(
				array(
					'first_name'     => 'Jane',
					'last_name'      => 'Doe',
					'nationality'    => 'British',
					'residence_city' => 'London',
					'birthdate'      => '1990-05-01',
					'birth_city'     => 'Leeds',
					'gender'         => 'female',
				),
				array(
					'first_name'     => 'Tim',
					'last_name'      => 'Doe',
					'nationality'    => 'British',
					'residence_city' => 'London',
					'birthdate'      => '2015-03-10',
					'birth_city'     => 'London',
					'gender'         => 'male',
				),
			),
			'ids'          => array(
				array(
					'guest_index' => 1,
					'doc_type'    => 'passport',
					'doc_number'  => 'X1234567',
				),
			),
			'counts'       => array(
				'guests' => 2,
				'houses' => 1,
			),
			'submitted_at' => '2026-06-29T14:32:00+00:00',
		);
	}

	public function test_payload_addresses_and_subject() {
		$p = \PedimentChild\Brevo::build_checkin_payload( $this->sample() );
		$this->assertSame( 'noreply@workationcastle.com', $p['sender']['email'] );
		$this->assertSame( 'info@workationcastle.com', $p['to'][0]['email'] );
		$this->assertStringContainsString( '2 guest', $p['subject'] );
		$this->assertStringContainsString( '1 house', $p['subject'] );
	}

	public function test_payload_lists_every_guest_and_id_in_both_bodies() {
		$p = \PedimentChild\Brevo::build_checkin_payload( $this->sample() );
		foreach ( array( 'htmlContent', 'textContent' ) as $body ) {
			$this->assertStringContainsString( 'Jane', $p[ $body ] );
			$this->assertStringContainsString( 'Tim', $p[ $body ] );
			$this->assertStringContainsString( 'X1234567', $p[ $body ] );
			$this->assertStringContainsString( 'Passport', $p[ $body ] );
		}
	}

	public function test_id_line_names_the_owning_guest() {
		// The single ID belongs to guest_index 1 (Tim Doe), not Jane (index 0).
		$p = \PedimentChild\Brevo::build_checkin_payload( $this->sample() );
		$this->assertStringContainsString( 'Tim Doe — Passport — X1234567', $p['textContent'] );
		$this->assertStringContainsString( 'Tim Doe: Passport — X1234567', $p['htmlContent'] );
	}
}
