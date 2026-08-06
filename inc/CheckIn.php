<?php
/**
 * Check-in: private CPT + canonical field/allowlist/cap config for the
 * multi-step guest check-in form. The block render derives its JSON config
 * from CheckIn::config() so PHP stays the single source of truth.
 *
 * @package Workation
 */

namespace Workation;

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
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( __CLASS__, 'admin_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( __CLASS__, 'render_column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
	}

	/** Register the private, admin-only submission CPT. */
	public static function register_cpt(): void {
		register_post_type(
			self::CPT,
			array(
				'labels'              => array(
					'name'          => __( 'Check-ins', 'workation' ),
					'singular_name' => __( 'Check-in', 'workation' ),
					'menu_name'     => __( 'Check-ins', 'workation' ),
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
				// Unique capability type so the singular meta caps (edit_post,
				// read_post, delete_post) namespace to this CPT. Only the
				// primitive/plural caps are remapped to manage_options — never
				// the singular meta caps, which would register manage_options
				// itself into WordPress's global $post_type_meta_caps and break
				// manage_options site-wide (hiding every Settings menu).
				'capability_type'     => array( 'wc_checkin', 'wc_checkins' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'create_posts'           => 'manage_options',
					'edit_posts'             => 'manage_options',
					'edit_others_posts'      => 'manage_options',
					'edit_published_posts'   => 'manage_options',
					'edit_private_posts'     => 'manage_options',
					'publish_posts'          => 'manage_options',
					'read_private_posts'     => 'manage_options',
					'delete_posts'           => 'manage_options',
					'delete_others_posts'    => 'manage_options',
					'delete_published_posts' => 'manage_options',
					'delete_private_posts'   => 'manage_options',
				),
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
					'label'    => __( 'First name', 'workation' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'last_name',
					'label'    => __( 'Last name', 'workation' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'nationality',
					'label'    => __( 'Nationality', 'workation' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'residence_city',
					'label'    => __( 'City of residence', 'workation' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'birthdate',
					'label'    => __( 'Birthdate', 'workation' ),
					'type'     => 'date',
					'required' => true,
				),
				array(
					'key'      => 'birth_city',
					'label'    => __( 'City of birth', 'workation' ),
					'type'     => 'text',
					'required' => true,
				),
				array(
					'key'      => 'gender',
					'label'    => __( 'Gender', 'workation' ),
					'type'     => 'radio',
					'required' => true,
					'options'  => array(
						array(
							'value' => 'male',
							'label' => __( 'Male', 'workation' ),
						),
						array(
							'value' => 'female',
							'label' => __( 'Female', 'workation' ),
						),
						array(
							'value' => 'other',
							'label' => __( 'Other', 'workation' ),
						),
					),
				),
			),
			'docTypes'    => array(
				array(
					'value' => 'identity_card',
					'label' => __( 'Identity card', 'workation' ),
				),
				array(
					'value' => 'drivers_license',
					'label' => __( 'Driver’s licence', 'workation' ),
				),
				array(
					'value' => 'passport',
					'label' => __( 'Passport', 'workation' ),
				),
			),
			'consentText' => __( 'I agree that Workation Castle processes and forwards my personal data to the Italian authorities.', 'workation' ),
			'strings'     => array(
				'countsHeading'  => __( 'Who’s checking in?', 'workation' ),
				'guestsLabel'    => __( 'How many guests are checking in (including children)?', 'workation' ),
				'housesLabel'    => __( 'How many accommodations did you book?', 'workation' ),
				/* translators: 1: current guest number, 2: total guests. */
				'guestHeading'   => __( 'Guest %1$d of %2$d', 'workation' ),
				/* translators: 1: current accommodation number, 2: total accommodations. */
				'houseHeading'   => __( 'Accommodation %1$d of %2$d', 'workation' ),
				'idIntro'        => __( 'We need one identity document for each accommodation you booked.', 'workation' ),
				'idGuestLabel'   => __( 'Which guest does this document belong to?', 'workation' ),
				'idTypeLabel'    => __( 'Type of identity document', 'workation' ),
				'idNumberLabel'  => __( 'Document number', 'workation' ),
				'reviewHeading'  => __( 'Review and submit', 'workation' ),
				'back'           => __( 'Back', 'workation' ),
				'next'           => __( 'Next', 'workation' ),
				'submit'         => __( 'Submit check-in', 'workation' ),
				'thankYou'       => __( 'Thank you — your check-in details have been received.', 'workation' ),
				'errorRequired'  => __( 'This field is required.', 'workation' ),
				'errorBirthdate' => __( 'Enter a valid birthdate (not in the future).', 'workation' ),
				'errorSubmit'    => __( 'Something went wrong submitting the form. Please try again or email info@workationcastle.com.', 'workation' ),
				'restoredNotice' => __( 'We restored your in-progress check-in.', 'workation' ),
				'startOver'      => __( 'Start over', 'workation' ),
			),
		);
	}

	/** Register the public (nonce-gated) submission route. */
	public static function register_rest(): void {
		register_rest_route(
			'workation/v1',
			'/check-in',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_submit' ),
				'permission_callback' => array( __CLASS__, 'verify_nonce' ),
			)
		);
	}

	/**
	 * Nonce gate. Guests aren't logged in, so we verify the wp_rest nonce.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return bool
	 */
	public static function verify_nonce( \WP_REST_Request $request ): bool {
		return (bool) wp_verify_nonce( (string) $request->get_header( 'X-WP-Nonce' ), 'wp_rest' );
	}

	/**
	 * Handle a submission: honeypot, validate, persist, email.
	 *
	 * @param \WP_REST_Request $request Incoming REST request.
	 * @return \WP_REST_Response
	 */
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

		$submission                 = $sanitized;
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

		$email                = Brevo::send_checkin_notification( $submission );
		$meta                 = get_post_meta( $post_id, '_wc_meta', true );
		$meta                 = is_array( $meta ) ? $meta : array();
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
			$errors['counts'] = __( 'Invalid number of guests or accommodations.', 'workation' );
		}

		$today = current_time( 'Y-m-d' );

		foreach ( $guests as $i => $g ) {
			$clean = array();
			foreach ( self::GUEST_TEXT_FIELDS as $field ) {
				$val = isset( $g[ $field ] ) ? sanitize_text_field( (string) $g[ $field ] ) : '';
				if ( '' === $val ) {
					$errors[ "guests.$i.$field" ] = __( 'This field is required.', 'workation' );
				}
				$clean[ $field ] = $val;
			}

			$birthdate = isset( $g['birthdate'] ) ? sanitize_text_field( (string) $g['birthdate'] ) : '';
			if ( ! self::is_valid_date( $birthdate ) || $birthdate > $today ) {
				$errors[ "guests.$i.birthdate" ] = __( 'Enter a valid birthdate.', 'workation' );
			}
			$clean['birthdate'] = $birthdate;

			$gender = isset( $g['gender'] ) ? sanitize_text_field( (string) $g['gender'] ) : '';
			if ( ! in_array( $gender, self::GENDERS, true ) ) {
				$errors[ "guests.$i.gender" ] = __( 'Select a gender.', 'workation' );
			}
			$clean['gender'] = $gender;

			$sanitized['guests'][] = $clean;
		}

		foreach ( $ids as $i => $id ) {
			$guest_index = isset( $id['guest_index'] ) && is_numeric( $id['guest_index'] )
				? (int) $id['guest_index']
				: -1;
			if ( $guest_index < 0 || $guest_index >= $guest_count ) {
				$errors[ "ids.$i.guest_index" ] = __( 'Select which guest this document belongs to.', 'workation' );
			}
			$doc_type = isset( $id['doc_type'] ) ? sanitize_text_field( (string) $id['doc_type'] ) : '';
			if ( ! in_array( $doc_type, self::DOC_TYPES, true ) ) {
				$errors[ "ids.$i.doc_type" ] = __( 'Select a document type.', 'workation' );
			}
			$doc_number = isset( $id['doc_number'] ) ? sanitize_text_field( (string) $id['doc_number'] ) : '';
			if ( '' === $doc_number ) {
				$errors[ "ids.$i.doc_number" ] = __( 'Enter the document number.', 'workation' );
			}
			$sanitized['ids'][] = array(
				'guest_index' => $guest_index,
				'doc_type'    => $doc_type,
				'doc_number'  => $doc_number,
			);
		}

		if ( empty( $raw['consent'] ) || true !== filter_var( $raw['consent'], FILTER_VALIDATE_BOOLEAN ) ) {
			$errors['consent'] = __( 'Consent is required.', 'workation' );
		}

		$sanitized['counts']  = array(
			'guests' => $guest_count,
			'houses' => $house_count,
		);
		$sanitized['consent'] = true;

		return array( $sanitized, $errors );
	}

	/**
	 * Admin list columns: title, guests, houses, email status, date.
	 *
	 * @param array $cols Existing column definitions.
	 * @return array
	 */
	public static function admin_columns( array $cols ): array {
		$out = array();
		foreach ( $cols as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'title' === $key ) {
				$out['wc_guests'] = __( 'Guests', 'workation' );
				$out['wc_houses'] = __( 'Accommodations', 'workation' );
				$out['wc_email']  = __( 'Email', 'workation' );
			}
		}
		return $out;
	}

	/**
	 * Render a custom admin column cell.
	 *
	 * @param string $col     Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function render_column( string $col, int $post_id ): void {
		$meta = get_post_meta( $post_id, '_wc_meta', true );
		$meta = is_array( $meta ) ? $meta : array();
		switch ( $col ) {
			case 'wc_guests':
				echo esc_html( (string) ( $meta['guest_count'] ?? '' ) );
				break;
			case 'wc_houses':
				echo esc_html( (string) ( $meta['house_count'] ?? '' ) );
				break;
			case 'wc_email':
				echo esc_html( (string) ( $meta['email_status'] ?? '' ) );
				break;
		}
	}

	/** Register the read-only submission meta box. */
	public static function add_meta_box(): void {
		add_meta_box(
			'wc_checkin_data',
			__( 'Check-in details', 'workation' ),
			array( __CLASS__, 'render_meta_box' ),
			self::CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Render the full submission as read-only HTML.
	 *
	 * @param \WP_Post $post Post object for the check-in submission.
	 */
	public static function render_meta_box( \WP_Post $post ): void {
		$guests = get_post_meta( $post->ID, '_wc_guests', true );
		$ids    = get_post_meta( $post->ID, '_wc_ids', true );
		$guests = is_array( $guests ) ? $guests : array();
		$ids    = is_array( $ids ) ? $ids : array();

		echo '<h3>' . esc_html__( 'Guests', 'workation' ) . '</h3><ol>';
		foreach ( $guests as $g ) {
			printf(
				'<li>%s %s — %s, %s, %s %s, %s %s</li>',
				esc_html( $g['first_name'] ?? '' ),
				esc_html( $g['last_name'] ?? '' ),
				esc_html( Brevo::gender_label( $g['gender'] ?? '' ) ),
				esc_html( $g['nationality'] ?? '' ),
				esc_html__( 'born', 'workation' ) . ' ' . esc_html( $g['birthdate'] ?? '' ),
				esc_html__( 'in', 'workation' ) . ' ' . esc_html( $g['birth_city'] ?? '' ),
				esc_html__( 'residing in', 'workation' ),
				esc_html( $g['residence_city'] ?? '' )
			);
		}
		echo '</ol>';

		echo '<h3>' . esc_html__( 'Identity documents (one per accommodation)', 'workation' ) . '</h3><ol>';
		foreach ( $ids as $id ) {
			$gi = isset( $id['guest_index'] ) ? (int) $id['guest_index'] : -1;
			printf(
				'<li>%s: %s — %s</li>',
				esc_html( Brevo::guest_name( $guests[ $gi ] ?? array() ) ),
				esc_html( Brevo::doc_type_label( $id['doc_type'] ?? '' ) ),
				esc_html( $id['doc_number'] ?? '' )
			);
		}
		echo '</ol>';
	}

	/**
	 * True for a real YYYY-MM-DD calendar date.
	 *
	 * @param string $value Date string to validate.
	 * @return bool
	 */
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
			/* translators: 1: guest count, 2: accommodation count, 3: date/time. */
			__( 'Check-in — %1$d guests, %2$d accommodations — %3$s', 'workation' ),
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
