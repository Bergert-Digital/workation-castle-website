<?php

/**
 * The child theme registers alignment block styles on the parent's
 * pediment/feature card so editors can pick left / centered / right
 * per card instead of being locked to the global left-aligned default.
 */
class FeatureCardStylesTest extends WP_UnitTestCase {

	public function test_alignment_styles_are_registered_for_the_feature_card() {
		$styles = WP_Block_Styles_Registry::get_instance()->get_registered_styles_for_block( 'pediment/feature' );

		$this->assertArrayHasKey( 'align-left', $styles );
		$this->assertArrayHasKey( 'align-center', $styles );
		$this->assertArrayHasKey( 'align-right', $styles );
	}

	public function test_left_is_the_default_style() {
		$styles = WP_Block_Styles_Registry::get_instance()->get_registered_styles_for_block( 'pediment/feature' );

		$this->assertTrue( ! empty( $styles['align-left']['is_default'] ) );
		$this->assertTrue( empty( $styles['align-center']['is_default'] ) );
		$this->assertTrue( empty( $styles['align-right']['is_default'] ) );
	}
}
