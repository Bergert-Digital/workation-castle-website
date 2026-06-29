<?php
/**
 * Check-in: private CPT + canonical field/allowlist/cap config for the
 * multi-step guest check-in form. The block render derives its JSON config
 * from CheckIn::config() so PHP stays the single source of truth.
 *
 * @package PedimentChild
 */

namespace PedimentChild;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CheckIn {

	const CPT = 'wc_checkin';

	const GENDERS   = array( 'male', 'female', 'other' );
	const DOC_TYPES = array( 'identity_card', 'drivers_license', 'passport' );

	const MIN_GUESTS = 1;
	const MAX_GUESTS = 20;
	const MIN_HOUSES = 1;
	const MAX_HOUSES = 10;

	/** Canonical guest text field keys (everything except gender). */
	const GUEST_TEXT_FIELDS = array(
		'first_name',
		'last_name',
		'nationality',
		'residence_city',
		'birth_city',
	);

	/** Wire all hooks. Called once from functions.php. */
	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
	}

	/** Register the private, admin-only submission CPT. */
	public static function register_cpt(): void {
		register_post_type(
			self::CPT,
			array(
				'labels'              => array(
					'name'          => __( 'Check-ins', 'pediment-child' ),
					'singular_name' => __( 'Check-in', 'pediment-child' ),
					'menu_name'     => __( 'Check-ins', 'pediment-child' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'menu_icon'           => 'dashicons-id',
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'supports'            => array( 'title' ),
			)
		);
	}

	/**
	 * Config consumed by the block front end. Labels live here so they are
	 * translatable in PHP; allowlists/caps here are the source of truth.
	 *
	 * @return array
	 */
	public static function config(): array {
		return array(
			'caps'        => array(
				'minGuests' => self::MIN_GUESTS,
				'maxGuests' => self::MAX_GUESTS,
				'minHouses' => self::MIN_HOUSES,
				'maxHouses' => self::MAX_HOUSES,
			),
			'guestFields' => array(
				array(
					'key'      => 'first_name',
					'label'    => __( 'First name', 'pediment-child' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'last_name',
					'label'    => __( 'Last name', 'pediment-child' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'nationality',
					'label'    => __( 'Nationality', 'pediment-child' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'residence_city',
					'label'    => __( 'City of residence', 'pediment-child' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'birthdate',
					'label'    => __( 'Birthdate', 'pediment-child' ),
					'type'     => 'date',
					'required' => true,
				),
				array(
					'key'      => 'birth_city',
					'label'    => __( 'City of birth', 'pediment-child' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'gender',
					'label'    => __( 'Gender', 'pediment-child' ),
					'type'     => 'radio',
					'required' => true,
					'options'  => array(
						array(
							'value' => 'male',
							'label' => __( 'Male', 'pediment-child' ),
						),
						array(
							'value' => 'female',
							'label' => __( 'Female', 'pediment-child' ),
						),
						array(
							'value' => 'other',
							'label' => __( 'Other', 'pediment-child' ),
						),
					),
				),
			),
			'docTypes'    => array(
				array(
					'value' => 'identity_card',
					'label' => __( 'Identity card', 'pediment-child' ),
				),
				array(
					'value' => 'drivers_license',
					'label' => __( 'Driver’s licence', 'pediment-child' ),
				),
				array(
					'value' => 'passport',
					'label' => __( 'Passport', 'pediment-child' ),
				),
			),
			'consentText' => __( 'I agree that Workation Castle processes and forwards my personal data to the Italian authorities.', 'pediment-child' ),
			'strings'     => array(
				'countsHeading'   => __( 'Who’s checking in?', 'pediment-child' ),
				'guestsLabel'     => __( 'How many guests are checking in (including children)?', 'pediment-child' ),
				'housesLabel'     => __( 'How many houses did you book?', 'pediment-child' ),
				/* translators: 1: current guest number, 2: total guests. */
				'guestHeading'    => __( 'Guest %1$d of %2$d', 'pediment-child' ),
				/* translators: 1: current house number, 2: total houses. */
				'houseHeading'    => __( 'House %1$d of %2$d', 'pediment-child' ),
				'idTypeLabel'     => __( 'Type of identity document', 'pediment-child' ),
				'idNumberLabel'   => __( 'Document number', 'pediment-child' ),
				'reviewHeading'   => __( 'Review and submit', 'pediment-child' ),
				'back'            => __( 'Back', 'pediment-child' ),
				'next'            => __( 'Next', 'pediment-child' ),
				'submit'          => __( 'Submit check-in', 'pediment-child' ),
				'thankYou'        => __( 'Thank you — your check-in details have been received.', 'pediment-child' ),
				'errorRequired'   => __( 'This field is required.', 'pediment-child' ),
				'errorSubmit'     => __( 'Something went wrong submitting the form. Please try again or email info@workationcastle.com.', 'pediment-child' ),
			),
		);
	}
}
