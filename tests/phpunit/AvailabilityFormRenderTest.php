<?php

class AvailabilityFormRenderTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();
		// Allow get_block_wrapper_attributes() to run outside a block render context.
		WP_Block_Supports::$block_to_render = array( 'blockName' => '', 'attrs' => array() );
	}

	public function tearDown(): void {
		WP_Block_Supports::$block_to_render = null;
		parent::tearDown();
	}

	public function test_renders_range_picker_root_and_native_fallback_inputs() {
		$html = pediment_child_render_availability_form( array() );
		$this->assertStringContainsString( 'class="avail-field wc-rangepicker"', $html );
		$this->assertStringContainsString( 'wc-rangepicker__fallback', $html );
		$this->assertStringContainsString( 'data-role="checkin"', $html );
		$this->assertStringContainsString( 'data-role="checkout"', $html );
		$this->assertSame( 1, substr_count( $html, 'data-role="checkin"' ) );
		$this->assertSame( 1, substr_count( $html, 'data-role="checkout"' ) );
	}

	public function test_guests_select_has_accessible_name_without_visible_label() {
		// Labels are dropped for the Airbnb-style pill bar; the guests select must
		// keep an accessible name via aria-label, and the standalone circle icon is gone.
		$html = pediment_child_render_availability_form( array() );
		$this->assertMatchesRegularExpression( '/<select[^>]*aria-label="Guests"/', $html );
		$this->assertStringNotContainsString( 'avail-icon--guest', $html );
	}

	public function test_native_inputs_use_default_param_names() {
		$html = pediment_child_render_availability_form( array() );
		$this->assertStringContainsString( 'name="checkIn"', $html );
		$this->assertStringContainsString( 'name="checkOut"', $html );
		$this->assertStringContainsString( 'name="adults"', $html );
	}

	public function test_param_names_are_overridable() {
		$html = pediment_child_render_availability_form(
			array( 'check_in_param' => 'arrivalDate', 'check_out_param' => 'departureDate' )
		);
		$this->assertStringContainsString( 'name="arrivalDate"', $html );
		$this->assertStringContainsString( 'name="departureDate"', $html );
		// data-role stays stable so the script still finds the inputs.
		$this->assertStringContainsString( 'data-role="checkin"', $html );
	}

	public function test_trigger_is_hidden_for_no_js_fallback() {
		$html = pediment_child_render_availability_form( array() );
		$this->assertMatchesRegularExpression(
			'/<button[^>]*class="wc-rangepicker__field"[^>]*hidden/',
			$html
		);
	}

	public function test_form_action_defaults_to_booking_url() {
		$html = pediment_child_render_availability_form( array() );
		$this->assertStringContainsString( 'action="https://workationcastle.holiduhost.com/"', $html );
	}

	public function test_hero_chrome_uses_shared_availability_form() {
		$html = pediment_child_workation_hero_chrome(
			array( 'headline' => 'Stay', 'primaryText' => 'Check availability' ),
			''
		);
		$this->assertStringContainsString( 'wc-rangepicker', $html );
		$this->assertStringContainsString( 'data-role="checkin"', $html );
		$this->assertStringContainsString( 'data-role="checkout"', $html );
	}

	public function test_hero_chrome_forwards_booking_params() {
		$html = pediment_child_workation_hero_chrome(
			array(
				'bookingUrl'   => 'https://example.test/book',
				'checkInParam' => 'arrivalDate',
				'primaryText'  => 'Book',
			),
			''
		);
		$this->assertStringContainsString( 'action="https://example.test/book"', $html );
		$this->assertStringContainsString( 'name="arrivalDate"', $html );
	}

	public function test_l10n_pulls_calendar_names_from_wp_locale() {
		$data = pediment_child_range_picker_l10n();
		$this->assertCount( 12, $data['months'] );
		$this->assertCount( 12, $data['monthsShort'] );
		$this->assertCount( 7, $data['weekdaysShort'] );
		// Sunday-first: index 0 is the locale's word for Sunday.
		$this->assertSame(
			$GLOBALS['wp_locale']->get_weekday_abbrev( $GLOBALS['wp_locale']->get_weekday( 0 ) ),
			$data['weekdaysShort'][0]
		);
		$this->assertSame( $GLOBALS['wp_locale']->month['01'], $data['months'][0] );
		$this->assertArrayHasKey( 'addDates', $data['i18n'] );
	}

	public function test_range_picker_script_is_enqueued_and_localized() {
		do_action( 'wp_enqueue_scripts' );
		$this->assertTrue( wp_script_is( 'workation-castle-range-picker', 'enqueued' ) );
		$data = wp_scripts()->get_data( 'workation-castle-range-picker', 'data' );
		$this->assertIsString( $data );
		$this->assertStringContainsString( 'wcRangePicker', $data );
		$this->assertStringContainsString( 'monthsShort', $data );
		$this->assertStringContainsString( 'startOfWeek', $data );
	}
}
