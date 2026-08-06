<?php
/**
 * Server-side render for workation/availability-form.
 *
 * @var array $attributes
 */

$action_url = isset( $attributes['actionUrl'] ) ? (string) $attributes['actionUrl'] : '';

echo workation_render_availability_form( // phpcs:ignore WordPress.Security.EscapeOutput
	array(
		'booking_url' => '' !== $action_url && '#' !== $action_url ? $action_url : null,
	)
);
