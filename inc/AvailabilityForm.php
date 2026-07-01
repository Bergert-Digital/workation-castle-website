<?php
/**
 * Shared "Check availability" form renderer + range-picker localization.
 *
 * Single source of truth for the availability form used by the hero
 * (pediment_child_workation_hero_chrome) and the availability-form block.
 * Renders one date-range field (progressively enhanced by
 * assets/js/range-picker.js) over two native date inputs that remain the only
 * submitted fields and the no-JS fallback.
 *
 * @package PedimentChild
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
function pediment_child_render_availability_form( array $args ): string {
	$booking_url     = ! empty( $args['booking_url'] ) ? (string) $args['booking_url'] : 'https://workationcastle.holiduhost.com/';
	$check_in_param  = ! empty( $args['check_in_param'] ) ? (string) $args['check_in_param'] : 'checkIn';
	$check_out_param = ! empty( $args['check_out_param'] ) ? (string) $args['check_out_param'] : 'checkOut';
	$adults_param    = ! empty( $args['adults_param'] ) ? (string) $args['adults_param'] : 'adults';
	$children_param  = ! empty( $args['children_param'] ) ? (string) $args['children_param'] : 'childrenAges';
	$submit_text     = ! empty( $args['submit_text'] ) ? (string) $args['submit_text'] : __( 'Check availability', 'pediment-child' );

	$arrival_id   = wp_unique_id( 'wc-arrival-' );
	$departure_id = wp_unique_id( 'wc-departure-' );
	$guests_id    = wp_unique_id( 'wc-guests-' );

	ob_start();
	?>
	<form class="avail" method="get" action="<?php echo esc_url( $booking_url ); ?>" aria-label="<?php esc_attr_e( 'Check availability', 'pediment-child' ); ?>">
		<div class="avail-field wc-rangepicker">
			<div class="wc-rangepicker__fallback">
				<div class="avail-field">
					<label for="<?php echo esc_attr( $arrival_id ); ?>"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Arrival', 'pediment-child' ); ?></label>
					<input type="date" id="<?php echo esc_attr( $arrival_id ); ?>" name="<?php echo esc_attr( $check_in_param ); ?>" data-role="checkin">
				</div>
				<div class="avail-field">
					<label for="<?php echo esc_attr( $departure_id ); ?>"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Departure', 'pediment-child' ); ?></label>
					<input type="date" id="<?php echo esc_attr( $departure_id ); ?>" name="<?php echo esc_attr( $check_out_param ); ?>" data-role="checkout">
				</div>
			</div>
			<button type="button" class="wc-rangepicker__field" aria-haspopup="dialog" aria-expanded="false" hidden>
				<span class="avail-icon" aria-hidden="true"></span>
				<span class="wc-rangepicker__label"><?php esc_html_e( 'Add dates', 'pediment-child' ); ?></span>
			</button>
		</div>
		<div class="avail-field select-wrap">
			<label for="<?php echo esc_attr( $guests_id ); ?>"><span class="avail-icon avail-icon--guest" aria-hidden="true"></span> <?php esc_html_e( 'Guests', 'pediment-child' ); ?></label>
			<select id="<?php echo esc_attr( $guests_id ); ?>" name="<?php echo esc_attr( $adults_param ); ?>">
				<option value="2"><?php esc_html_e( '2 guests', 'pediment-child' ); ?></option>
				<option value="1"><?php esc_html_e( '1 guest', 'pediment-child' ); ?></option>
				<option value="3"><?php esc_html_e( '3 guests', 'pediment-child' ); ?></option>
				<option value="4"><?php esc_html_e( '4 guests', 'pediment-child' ); ?></option>
				<option value="5"><?php esc_html_e( '5 guests', 'pediment-child' ); ?></option>
				<option value="6"><?php esc_html_e( '6 guests', 'pediment-child' ); ?></option>
				<option value="7"><?php esc_html_e( '7 guests', 'pediment-child' ); ?></option>
				<option value="8"><?php esc_html_e( '8 guests', 'pediment-child' ); ?></option>
				<option value="9"><?php esc_html_e( '9 guests', 'pediment-child' ); ?></option>
			</select>
		</div>
		<input type="hidden" name="<?php echo esc_attr( $children_param ); ?>" value="">
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
function pediment_child_range_picker_l10n(): array {
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
			'addDates'    => __( 'Add dates', 'pediment-child' ),
			'clear'       => __( 'Clear', 'pediment-child' ),
			'close'       => __( 'Close', 'pediment-child' ),
			'dialogLabel' => __( 'Choose your dates', 'pediment-child' ),
			'nextMonth'   => __( 'Next month', 'pediment-child' ),
			'night'       => __( 'night', 'pediment-child' ),
			'nights'      => __( 'nights', 'pediment-child' ),
			'prevMonth'   => __( 'Previous month', 'pediment-child' ),
		),
	);
}
