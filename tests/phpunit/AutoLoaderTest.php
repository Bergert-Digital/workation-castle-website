<?php

class AutoLoaderTest extends WP_UnitTestCase {
	public function test_loader_function_exists() {
		$this->assertTrue( function_exists( 'workation_register_blocks' ) );
	}

	public function test_loader_handles_missing_build_dir_gracefully() {
		workation_register_blocks( '/nonexistent/path' );
		$this->assertTrue( true );
	}

	public function test_explicit_missing_dir_does_not_flag_a_build_problem() {
		remove_all_actions( 'admin_notices' );

		workation_register_blocks( '/nonexistent/path' );

		$this->assertFalse(
			has_action( 'admin_notices' ),
			'Only the theme\'s own build/blocks should raise the notice; an explicit path must not.'
		);
	}

	public function test_flagging_hooks_the_notice_once_however_often_it_runs() {
		remove_all_actions( 'admin_notices' );

		workation_flag_missing_build();
		workation_flag_missing_build();

		$hooked = 0;
		foreach ( $GLOBALS['wp_filter']['admin_notices']->callbacks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( 'workation_render_missing_build_notice' === $callback['function'] ) {
					++$hooked;
				}
			}
		}

		$this->assertSame( 1, $hooked, 'init can fire twice; the notice must not double up.' );
	}

	public function test_missing_build_notice_names_the_directory_and_the_fix() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		workation_render_missing_build_notice();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'notice-error', $html );
		$this->assertStringContainsString( WORKATION_DIR . '/build/blocks', $html );
		$this->assertStringContainsString( 'workation.zip', $html );
	}

	public function test_missing_build_notice_is_hidden_from_users_who_cannot_switch_themes() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		ob_start();
		workation_render_missing_build_notice();
		$html = ob_get_clean();

		$this->assertSame( '', trim( $html ) );
	}

	public function test_loader_registers_blocks_from_build_dir() {
		$tmp = sys_get_temp_dir() . '/workation-test-blocks-' . uniqid();
		mkdir( $tmp . '/dummy-block', 0777, true );
		file_put_contents(
			$tmp . '/dummy-block/block.json',
			wp_json_encode(
				array(
					'apiVersion' => 3,
					'name'       => 'workation/dummy',
					'title'      => 'Dummy',
					'category'   => 'design',
					'attributes' => array( 'text' => array( 'type' => 'string', 'default' => '' ) ),
				)
			)
		);

		workation_register_blocks( $tmp );

		$registry = WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'workation/dummy' ) );

		$registry->unregister( 'workation/dummy' );
	}
}
