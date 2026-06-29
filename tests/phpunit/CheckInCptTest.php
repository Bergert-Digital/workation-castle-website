<?php
// tests/phpunit/CheckInCptTest.php

class CheckInCptTest extends WP_UnitTestCase {

	public function test_cpt_is_registered_and_private() {
		$this->assertTrue( post_type_exists( \PedimentChild\CheckIn::CPT ) );
		$obj = get_post_type_object( \PedimentChild\CheckIn::CPT );
		$this->assertFalse( $obj->public );
		$this->assertFalse( $obj->publicly_queryable );
		$this->assertTrue( $obj->show_ui );
	}

	public function test_config_exposes_caps_and_allowlists() {
		$config = \PedimentChild\CheckIn::config();
		$this->assertSame( 20, $config['caps']['maxGuests'] );
		$this->assertSame( 1, $config['caps']['minGuests'] );
		$this->assertSame( 10, $config['caps']['maxHouses'] );

		$genders = array_column( $config['guestFields'], 'key' );
		$this->assertContains( 'gender', $genders );
		$this->assertContains( 'first_name', $genders );

		$doc_values = array_column( $config['docTypes'], 'value' );
		$this->assertSame( \PedimentChild\CheckIn::DOC_TYPES, $doc_values );
	}
}
