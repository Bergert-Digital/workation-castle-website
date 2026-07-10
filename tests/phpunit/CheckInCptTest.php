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

	public function test_cpt_is_admin_only() {
		$cpt     = \PedimentChild\CheckIn::CPT;
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => $cpt,
				'post_status' => 'private',
			)
		);

		$editor_id = self::factory()->user->create( array( 'role' => 'editor' ) );
		$admin_id  = self::factory()->user->create( array( 'role' => 'administrator' ) );

		// Editor must be locked out of per-post and type-level caps.
		$this->assertFalse(
			user_can( $editor_id, 'edit_post', $post_id ),
			'Editor must not have edit_post on wc_checkin.'
		);
		$this->assertFalse(
			user_can( $editor_id, 'read_post', $post_id ),
			'Editor must not have read_post on wc_checkin.'
		);
		$this->assertFalse(
			user_can( $editor_id, get_post_type_object( $cpt )->cap->edit_posts ),
			'Editor must not have the type-level edit_posts cap for wc_checkin.'
		);

		// Administrator must have access.
		$this->assertTrue(
			user_can( $admin_id, 'edit_post', $post_id ),
			'Administrator must have edit_post on wc_checkin.'
		);
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
