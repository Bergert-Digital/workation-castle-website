<?php
/**
 * Shared "Check availability" form renderer + range-picker localization.
 *
 * Single source of truth for the availability form used by the hero
 * (workation_workation_hero_chrome) and the availability-form block.
 * Renders one date-range field (progressively enhanced by
 * assets/js/range-picker.js) over two native date inputs that remain the only
 * submitted fields and the no-JS fallback.
 *
 * @package Workation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the availability form markup.
 *
 * @param array $args {
 *     Optional. All keys default to the Workation Castle booking values.
 *
 *     @type string $booking_url     Form action (Holidu booking URL).
 *     @type string $check_in_param  Name attribute for the arrival input.
 *     @type string $check_out_param Name attribute for the departure input.
 *     @type string $adults_param    Name attribute for the guests select.
 *     @type string $children_param  Name attribute for the hidden children-ages input.
 *     @type string $submit_text     Submit button label.
 * }
 * @return string
 */
function workation_render_availability_form( array $args ): string {
	$booking_url     = ! empty( $args['booking_url'] ) ? (string) $args['booking_url'] : 'https://workationcastle.holiduhost.com/';
	$check_in_param  = ! empty( $args['check_in_param'] ) ? (string) $args['check_in_param'] : 'checkIn';
	$check_out_param = ! empty( $args['check_out_param'] ) ? (string) $args['check_out_param'] : 'checkOut';
	$adults_param    = ! empty( $args['adults_param'] ) ? (string) $args['adults_param'] : 'adults';
	$children_param  = ! empty( $args['children_param'] ) ? (string) $args['children_param'] : 'childrenAges';
	$submit_text     = ! empty( $args['submit_text'] ) ? (string) $args['submit_text'] : __( 'Check availability', 'workation' );

	// A GET form discards its action's query string on submit, so the current
	// language rides along as a field instead. See inc/Booking.php.
	$language = function_exists( 'workation_booking_language' ) ? workation_booking_language() : '';

	$arrival_id   = wp_unique_id( 'wc-arrival-' );
	$departure_id = wp_unique_id( 'wc-departure-' );

	ob_start();
	?>
	<form class="avail" method="get" action="<?php echo esc_url( $booking_url ); ?>" aria-label="<?php esc_attr_e( 'Check availability', 'workation' ); ?>">
		<div class="avail-field wc-rangepicker">
			<div class="wc-rangepicker__fallback">
				<div class="avail-field">
					<label for="<?php echo esc_attr( $arrival_id ); ?>"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Arrival', 'workation' ); ?></label>
					<input type="date" id="<?php echo esc_attr( $arrival_id ); ?>" name="<?php echo esc_attr( $check_in_param ); ?>" data-role="checkin">
				</div>
				<div class="avail-field">
					<label for="<?php echo esc_attr( $departure_id ); ?>"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Departure', 'workation' ); ?></label>
					<input type="date" id="<?php echo esc_attr( $departure_id ); ?>" name="<?php echo esc_attr( $check_out_param ); ?>" data-role="checkout">
				</div>
			</div>
			<button type="button" class="wc-rangepicker__field" aria-haspopup="dialog" aria-expanded="false" hidden>
				<span class="wc-rangepicker__label"><?php esc_html_e( 'Select dates', 'workation' ); ?></span>
			</button>
		</div>
		<div class="avail-field select-wrap">
			<select name="<?php echo esc_attr( $adults_param ); ?>" aria-label="<?php esc_attr_e( 'Guests', 'workation' ); ?>">
				<option value="2"><?php esc_html_e( '2 guests', 'workation' ); ?></option>
				<option value="1"><?php esc_html_e( '1 guest', 'workation' ); ?></option>
				<option value="3"><?php esc_html_e( '3 guests', 'workation' ); ?></option>
				<option value="4"><?php esc_html_e( '4 guests', 'workation' ); ?></option>
				<option value="5"><?php esc_html_e( '5 guests', 'workation' ); ?></option>
				<option value="6"><?php esc_html_e( '6 guests', 'workation' ); ?></option>
				<option value="7"><?php esc_html_e( '7 guests', 'workation' ); ?></option>
				<option value="8"><?php esc_html_e( '8 guests', 'workation' ); ?></option>
				<option value="9"><?php esc_html_e( '9 guests', 'workation' ); ?></option>
			</select>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $children_param ); ?>" value="">
		<?php if ( '' !== $language ) : ?>
			<input type="hidden" name="language" value="<?php echo esc_attr( $language ); ?>">
		<?php endif; ?>
		<div class="avail-submit"><button type="submit" class="wc-btn wc-btn-yellow"><?php echo esc_html( $submit_text ); ?> <span class="arr" aria-hidden="true">&rarr;</span></button></div>
	</form>
	<?php
	return (string) ob_get_clean();
}

/**
 * Localization payload for the range picker.
 *
 * Month/weekday names come from WordPress's active locale, so translation
 * plugins that switch the locale per language localize the picker with no JS
 * changes. UI strings go through __() to land in the translation catalog.
 *
 * @return array
 */
function workation_range_picker_l10n(): array {
	$wp_locale = $GLOBALS['wp_locale'];

	$months       = array_values( $wp_locale->month );
	$months_short = array();
	foreach ( $wp_locale->month as $full ) {
		$months_short[] = $wp_locale->get_month_abbrev( $full );
	}
	$weekdays_short = array();
	foreach ( $wp_locale->weekday as $full ) {
		$weekdays_short[] = $wp_locale->get_weekday_abbrev( $full );
	}

	return array(
		'months'        => $months,
		'monthsShort'   => $months_short,
		'weekdaysShort' => $weekdays_short, // Index 0 = Sunday.
		'startOfWeek'   => (int) get_option( 'start_of_week', 1 ),
		'i18n'          => array(
			'addDates'    => __( 'Select dates', 'workation' ),
			'clear'       => __( 'Clear', 'workation' ),
			'close'       => __( 'Close', 'workation' ),
			'dialogLabel' => __( 'Choose your dates', 'workation' ),
			'nextMonth'   => __( 'Next month', 'workation' ),
			'night'       => __( 'night', 'workation' ),
			'nights'      => __( 'nights', 'workation' ),
			'prevMonth'   => __( 'Previous month', 'workation' ),
		),
	);
}
