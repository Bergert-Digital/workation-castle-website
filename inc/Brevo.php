<?php
/**
 * Brevo transactional email for check-in submissions.
 *
 * Build_checkin_payload() is pure (no network) so it is unit-testable;
 * send_checkin_notification() (added in the next task) performs the HTTP call.
 *
 * @package PedimentChild
 */

namespace PedimentChild;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Brevo {

	const SENDER_EMAIL = 'noreply@workationcastle.com';
	const SENDER_NAME  = 'Workation Castle';
	const TO_EMAIL     = 'info@workationcastle.com';

	const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

	/**
	 * Human label for a doc_type allowlist value.
	 *
	 * @param string $value Allowlist value (e.g. 'identity_card').
	 * @return string Human-readable label.
	 */
	public static function doc_type_label( string $value ): string {
		$map = array(
			'identity_card'   => __( 'Identity card', 'pediment-child' ),
			'drivers_license' => __( "Driver\u{2019}s licence", 'pediment-child' ),
			'passport'        => __( 'Passport', 'pediment-child' ),
		);
		return $map[ $value ] ?? $value;
	}

	/**
	 * Human label for a gender allowlist value.
	 *
	 * @param string $value Allowlist value (e.g. 'male').
	 * @return string Human-readable label.
	 */
	public static function gender_label( string $value ): string {
		$map = array(
			'male'   => __( 'Male', 'pediment-child' ),
			'female' => __( 'Female', 'pediment-child' ),
			'other'  => __( 'Other', 'pediment-child' ),
		);
		return $map[ $value ] ?? $value;
	}

	/**
	 * Build the Brevo /v3/smtp/email request body for a submission.
	 *
	 * @param array $submission guests, ids, counts, submitted_at.
	 * @return array
	 */
	public static function build_checkin_payload( array $submission ): array {
		$guests = $submission['guests'] ?? array();
		$ids    = $submission['ids'] ?? array();
		$gc     = (int) ( $submission['counts']['guests'] ?? count( $guests ) );
		$hc     = (int) ( $submission['counts']['houses'] ?? count( $ids ) );

		$subject = sprintf(
			/* translators: 1: guest count, 2: house count. */
			__( 'New check-in: %1$d guests, %2$d houses', 'pediment-child' ),
			$gc,
			$hc
		);

		// Text body.
		$lines   = array();
		$lines[] = $subject;
		$lines[] = '';
		$lines[] = __( 'Guests', 'pediment-child' );
		$lines[] = '------';
		foreach ( $guests as $i => $g ) {
			$lines[] = sprintf(
				'%d. %s %s — %s, %s, born %s in %s, %s',
				$i + 1,
				$g['first_name'] ?? '',
				$g['last_name'] ?? '',
				self::gender_label( $g['gender'] ?? '' ),
				$g['nationality'] ?? '',
				$g['birthdate'] ?? '',
				$g['birth_city'] ?? '',
				/* translators: %s: city of residence. */
				sprintf( __( 'residing in %s', 'pediment-child' ), $g['residence_city'] ?? '' )
			);
		}
		$lines[] = '';
		$lines[] = __( 'Identity documents (one per house)', 'pediment-child' );
		$lines[] = '------';
		foreach ( $ids as $i => $id ) {
			$lines[] = sprintf(
				'%d. %s — %s',
				$i + 1,
				self::doc_type_label( $id['doc_type'] ?? '' ),
				$id['doc_number'] ?? ''
			);
		}
		$text = implode( "\n", $lines );

		// HTML body.
		$html  = '<h2>' . esc_html( $subject ) . '</h2>';
		$html .= '<h3>' . esc_html__( 'Guests', 'pediment-child' ) . '</h3><ol>';
		foreach ( $guests as $g ) {
			$html .= '<li>' . esc_html(
				sprintf(
					'%s %s — %s, %s, born %s in %s, residing in %s',
					$g['first_name'] ?? '',
					$g['last_name'] ?? '',
					self::gender_label( $g['gender'] ?? '' ),
					$g['nationality'] ?? '',
					$g['birthdate'] ?? '',
					$g['birth_city'] ?? '',
					$g['residence_city'] ?? ''
				)
			) . '</li>';
		}
		$html .= '</ol><h3>' . esc_html__( 'Identity documents (one per house)', 'pediment-child' ) . '</h3><ol>';
		foreach ( $ids as $id ) {
			$html .= '<li>' . esc_html(
				self::doc_type_label( $id['doc_type'] ?? '' ) . ' — ' . ( $id['doc_number'] ?? '' )
			) . '</li>';
		}
		$html .= '</ol>';

		return array(
			'sender'      => array(
				'name'  => self::SENDER_NAME,
				'email' => self::SENDER_EMAIL,
			),
			'to'          => array(
				array( 'email' => self::TO_EMAIL ),
			),
			'subject'     => $subject,
			'htmlContent' => $html,
			'textContent' => $text,
		);
	}

	/** Resolve the Brevo API key: constant first, then environment. */
	public static function api_key(): string {
		if ( defined( 'WORKATION_BREVO_API_KEY' ) && WORKATION_BREVO_API_KEY ) {
			return (string) WORKATION_BREVO_API_KEY;
		}
		$env = getenv( 'BREVO_API_KEY' );
		return $env ? (string) $env : '';
	}

	/**
	 * Send the check-in notification email. Never throws; a missing key or a
	 * failed call returns a status the caller records — the submission is
	 * already persisted by then.
	 *
	 * @param array $submission guests, ids, counts, submitted_at.
	 * @return array{status:string,error:?string}
	 */
	public static function send_checkin_notification( array $submission ): array {
		$key = self::api_key();
		if ( '' === $key ) {
			error_log( '[check-in] Brevo API key missing; email skipped.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array(
				'status' => 'skipped',
				'error'  => null,
			);
		}

		$payload  = self::build_checkin_payload( $submission );
		$response = wp_remote_post(
			self::ENDPOINT,
			array(
				'timeout' => 15,
				'headers' => array(
					'accept'       => 'application/json',
					'content-type' => 'application/json',
					'api-key'      => $key,
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			$msg = $response->get_error_message();
			error_log( '[check-in] Brevo request error: ' . $msg ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array(
				'status' => 'failed',
				'error'  => $msg,
			);
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			$body = wp_remote_retrieve_body( $response );
			error_log( '[check-in] Brevo non-2xx (' . $code . '): ' . $body ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array(
				'status' => 'failed',
				'error'  => 'HTTP ' . $code,
			);
		}

		return array(
			'status' => 'sent',
			'error'  => null,
		);
	}
}
