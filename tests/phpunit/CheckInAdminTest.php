<?php
// tests/phpunit/CheckInAdminTest.php

class CheckInAdminTest extends WP_UnitTestCase {

	private function make_post(): int {
		$id = self::factory()->post->create( array( 'post_type' => \PedimentChild\CheckIn::CPT ) );
		update_post_meta( $id, '_wc_guests', array(
			array(
				'first_name' => 'Jane', 'last_name' => 'Doe', 'nationality' => 'British',
				'residence_city' => 'London', 'birthdate' => '1990-05-01',
				'birth_city' => 'Leeds', 'gender' => 'female',
			),
		) );
		update_post_meta( $id, '_wc_ids', array( array( 'doc_type' => 'passport', 'doc_number' => 'X1' ) ) );
		update_post_meta( $id, '_wc_meta', array( 'guest_count' => 1, 'house_count' => 1, 'email_status' => 'sent' ) );
		return $id;
	}

	public function test_columns_include_guest_and_house_counts() {
		$cols = \PedimentChild\CheckIn::admin_columns( array( 'cb' => '', 'title' => 'Title', 'date' => 'Date' ) );
		$this->assertArrayHasKey( 'wc_guests', $cols );
		$this->assertArrayHasKey( 'wc_houses', $cols );
		$this->assertArrayHasKey( 'wc_email', $cols );
	}

	public function test_column_renders_guest_count() {
		$id = $this->make_post();
		ob_start();
		\PedimentChild\CheckIn::render_column( 'wc_guests', $id );
		$this->assertSame( '1', trim( ob_get_clean() ) );
	}

	public function test_meta_box_lists_guest_and_id() {
		$id  = $this->make_post();
		ob_start();
		\PedimentChild\CheckIn::render_meta_box( get_post( $id ) );
		$out = ob_get_clean();
		$this->assertStringContainsString( 'Jane', $out );
		$this->assertStringContainsString( 'Doe', $out );
		$this->assertStringContainsString( 'X1', $out );
		$this->assertStringContainsString( 'Passport', $out );
	}
}
