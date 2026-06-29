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
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
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

	/** Register the public (nonce-gated) submission route. */
	public static function register_rest(): void {
		register_rest_route(
			'pediment-child/v1',
			'/check-in',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_submit' ),
				'permission_callback' => array( __CLASS__, 'verify_nonce' ),
			)
		);
	}

	/** Nonce gate. Guests aren't logged in, so we verify the wp_rest nonce. */
	public static function verify_nonce( \WP_REST_Request $request ): bool {
		return (bool) wp_verify_nonce( (string) $request->get_header( 'X-WP-Nonce' ), 'wp_rest' );
	}

	/** Handle a submission: honeypot, validate, persist, email. */
	public static function handle_submit( \WP_REST_Request $request ): \WP_REST_Response {
		$raw = $request->get_json_params();
		if ( ! is_array( $raw ) ) {
			$raw = $request->get_params();
		}

		// Honeypot: a filled "website" field means a bot. Accept silently,
		// persist nothing.
		if ( ! empty( $raw['website'] ) ) {
			return new \WP_REST_Response( array( 'ok' => true ), 200 );
		}

		list( $sanitized, $errors ) = self::validate_submission( $raw );
		if ( ! empty( $errors ) ) {
			return new \WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => $errors,
				),
				400
			);
		}

		$submission = $sanitized;
		$submission['submitted_at'] = current_time( 'c' );

		$post_id = self::persist( $submission );
		if ( is_wp_error( $post_id ) ) {
			return new \WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => array( 'server' => $post_id->get_error_message() ),
				),
				500
			);
		}

		$email = Brevo::send_checkin_notification( $submission );
		$meta  = get_post_meta( $post_id, '_wc_meta', true );
		$meta  = is_array( $meta ) ? $meta : array();
		$meta['email_status'] = $email['status'];
		if ( ! empty( $email['error'] ) ) {
			$meta['email_error'] = $email['error'];
		}
		update_post_meta( $post_id, '_wc_meta', $meta );

		return new \WP_REST_Response( array( 'ok' => true ), 200 );
	}

	/**
	 * Validate + sanitize a raw submission.
	 *
	 * @param array $raw Decoded request body.
	 * @return array{0:array,1:array<string,string>} [ sanitized, errors ]
	 */
	public static function validate_submission( array $raw ): array {
		$errors    = array();
		$sanitized = array(
			'guests' => array(),
			'ids'    => array(),
			'counts' => array(
				'guests' => 0,
				'houses' => 0,
			),
		);

		$guest_count = isset( $raw['counts']['guests'] ) ? (int) $raw['counts']['guests'] : 0;
		$house_count = isset( $raw['counts']['houses'] ) ? (int) $raw['counts']['houses'] : 0;
		$guests      = isset( $raw['guests'] ) && is_array( $raw['guests'] ) ? array_values( $raw['guests'] ) : array();
		$ids         = isset( $raw['ids'] ) && is_array( $raw['ids'] ) ? array_values( $raw['ids'] ) : array();

		// Caps + count/array-length agreement.
		if (
			$guest_count < self::MIN_GUESTS || $guest_count > self::MAX_GUESTS ||
			$house_count < self::MIN_HOUSES || $house_count > self::MAX_HOUSES ||
			count( $guests ) !== $guest_count ||
			count( $ids ) !== $house_count
		) {
			$errors['counts'] = __( 'Invalid number of guests or houses.', 'pediment-child' );
		}

		$today = current_time( 'Y-m-d' );

		foreach ( $guests as $i => $g ) {
			$clean = array();
			foreach ( self::GUEST_TEXT_FIELDS as $field ) {
				$val = isset( $g[ $field ] ) ? sanitize_text_field( (string) $g[ $field ] ) : '';
				if ( '' === $val ) {
					$errors[ "guests.$i.$field" ] = __( 'This field is required.', 'pediment-child' );
				}
				$clean[ $field ] = $val;
			}

			$birthdate = isset( $g['birthdate'] ) ? sanitize_text_field( (string) $g['birthdate'] ) : '';
			if ( ! self::is_valid_date( $birthdate ) || $birthdate > $today ) {
				$errors[ "guests.$i.birthdate" ] = __( 'Enter a valid birthdate.', 'pediment-child' );
			}
			$clean['birthdate'] = $birthdate;

			$gender = isset( $g['gender'] ) ? sanitize_text_field( (string) $g['gender'] ) : '';
			if ( ! in_array( $gender, self::GENDERS, true ) ) {
				$errors[ "guests.$i.gender" ] = __( 'Select a gender.', 'pediment-child' );
			}
			$clean['gender'] = $gender;

			$sanitized['guests'][] = $clean;
		}

		foreach ( $ids as $i => $id ) {
			$doc_type = isset( $id['doc_type'] ) ? sanitize_text_field( (string) $id['doc_type'] ) : '';
			if ( ! in_array( $doc_type, self::DOC_TYPES, true ) ) {
				$errors[ "ids.$i.doc_type" ] = __( 'Select a document type.', 'pediment-child' );
			}
			$doc_number = isset( $id['doc_number'] ) ? sanitize_text_field( (string) $id['doc_number'] ) : '';
			if ( '' === $doc_number ) {
				$errors[ "ids.$i.doc_number" ] = __( 'Enter the document number.', 'pediment-child' );
			}
			$sanitized['ids'][] = array(
				'doc_type'   => $doc_type,
				'doc_number' => $doc_number,
			);
		}

		if ( empty( $raw['consent'] ) || true !== filter_var( $raw['consent'], FILTER_VALIDATE_BOOLEAN ) ) {
			$errors['consent'] = __( 'Consent is required.', 'pediment-child' );
		}

		$sanitized['counts'] = array(
			'guests' => $guest_count,
			'houses' => $house_count,
		);
		$sanitized['consent'] = true;

		return array( $sanitized, $errors );
	}

	/** True for a real YYYY-MM-DD calendar date. */
	private static function is_valid_date( string $value ): bool {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
			return false;
		}
		list( $y, $m, $d ) = array_map( 'intval', explode( '-', $value ) );
		return checkdate( $m, $d, $y );
	}

	/**
	 * Persist a validated submission as a private wc_checkin post.
	 *
	 * @param array $submission Validated/sanitized data + submitted_at.
	 * @return int|\WP_Error Post ID or error.
	 */
	public static function persist( array $submission ) {
		$gc    = (int) $submission['counts']['guests'];
		$hc    = (int) $submission['counts']['houses'];
		$title = sprintf(
			/* translators: 1: guest count, 2: house count, 3: date/time. */
			__( 'Check-in — %1$d guests, %2$d houses — %3$s', 'pediment-child' ),
			$gc,
			$hc,
			$submission['submitted_at']
		);

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT,
				'post_status' => 'private',
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, '_wc_guests', $submission['guests'] );
		update_post_meta( $post_id, '_wc_ids', $submission['ids'] );
		update_post_meta(
			$post_id,
			'_wc_consent',
			array(
				'given' => true,
				'at'    => $submission['submitted_at'],
			)
		);
		update_post_meta(
			$post_id,
			'_wc_meta',
			array(
				'submitted_at' => $submission['submitted_at'],
				'guest_count'  => $gc,
				'house_count'  => $hc,
				'email_status' => 'pending',
			)
		);

		return $post_id;
	}
}
