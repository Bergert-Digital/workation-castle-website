<?php
// tests/phpunit/CheckInRestTest.php

class CheckInRestTest extends WP_UnitTestCase {

	protected $server;

	public function setUp(): void {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );
		putenv( 'BREVO_API_KEY' ); // ensure email is skipped (no network)
	}

	public function tearDown(): void {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tearDown();
	}

	private function valid_body(): array {
		return array(
			'website' => '', // honeypot empty
			'consent' => true,
			'counts'  => array( 'guests' => 2, 'houses' => 1 ),
			'guests'  => array(
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
			'ids'     => array(
				array( 'guest_index' => 0, 'doc_type' => 'passport', 'doc_number' => 'X1234567' ),
			),
		);
	}

	private function request( array $body ): \WP_REST_Response {
		$req = new \WP_REST_Request( 'POST', '/workation/v1/check-in' );
		$req->set_header( 'Content-Type', 'application/json' );
		$req->set_header( 'X-WP-Nonce', wp_create_nonce( 'wp_rest' ) );
		$req->set_body( wp_json_encode( $body ) );
		return $this->server->dispatch( $req );
	}

	public function test_valid_submission_creates_post_with_meta() {
		$res  = $this->request( $this->valid_body() );
		$this->assertSame( 200, $res->get_status() );
		$data = $res->get_data();
		$this->assertTrue( $data['ok'] );

		$posts = get_posts(
			array(
				'post_type'   => \Workation\CheckIn::CPT,
				'post_status' => 'private',
				'numberposts' => -1,
			)
		);
		$this->assertCount( 1, $posts );
		$guests = get_post_meta( $posts[0]->ID, '_wc_guests', true );
		$this->assertCount( 2, $guests );
		$this->assertSame( 'Jane', $guests[0]['first_name'] );
		$ids = get_post_meta( $posts[0]->ID, '_wc_ids', true );
		$this->assertSame( 'X1234567', $ids[0]['doc_number'] );
		$this->assertSame( 0, $ids[0]['guest_index'] );
		$meta = get_post_meta( $posts[0]->ID, '_wc_meta', true );
		$this->assertSame( 'skipped', $meta['email_status'] );
	}

	public function test_rejects_out_of_range_guest_index() {
		$body = $this->valid_body();
		$body['ids'][0]['guest_index'] = 5; // only 2 guests (valid 0-1)
		$res  = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'ids.0.guest_index', $res->get_data()['errors'] );
	}

	public function test_rejects_missing_guest_index() {
		$body = $this->valid_body();
		unset( $body['ids'][0]['guest_index'] );
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'ids.0.guest_index', $res->get_data()['errors'] );
	}

	public function test_rejects_bad_gender() {
		$body = $this->valid_body();
		$body['guests'][0]['gender'] = 'martian';
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'guests.0.gender', $res->get_data()['errors'] );
	}

	public function test_rejects_bad_doc_type() {
		$body = $this->valid_body();
		$body['ids'][0]['doc_type'] = 'library_card';
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'ids.0.doc_type', $res->get_data()['errors'] );
	}

	public function test_rejects_future_birthdate() {
		$body = $this->valid_body();
		$body['guests'][0]['birthdate'] = '2999-01-01';
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'guests.0.birthdate', $res->get_data()['errors'] );
	}

	public function test_requires_consent() {
		$body = $this->valid_body();
		$body['consent'] = false;
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'consent', $res->get_data()['errors'] );
	}

	public function test_rejects_over_cap_guests() {
		$body = $this->valid_body();
		$body['counts']['guests'] = 21;
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'counts', $res->get_data()['errors'] );
	}

	public function test_rejects_count_mismatch() {
		$body = $this->valid_body();
		$body['counts']['guests'] = 3; // but only 2 guest records sent
		$res = $this->request( $body );
		$this->assertSame( 400, $res->get_status() );
		$this->assertArrayHasKey( 'counts', $res->get_data()['errors'] );
	}

	public function test_honeypot_silently_accepts_without_persisting() {
		$body = $this->valid_body();
		$body['website'] = 'http://spam.example';
		$res = $this->request( $body );
		$this->assertSame( 200, $res->get_status() );
		$this->assertTrue( $res->get_data()['ok'] );
		$posts = get_posts( array( 'post_type' => \Workation\CheckIn::CPT, 'numberposts' => -1, 'post_status' => 'any' ) );
		$this->assertCount( 0, $posts );
	}
}
