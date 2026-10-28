<?php
/** @var array $attributes */

$lat        = isset( $attributes['lat'] ) ? (string) $attributes['lat'] : '';
$lng        = isset( $attributes['lng'] ) ? (string) $attributes['lng'] : '';
$dest_title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';

if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
	return '';
}

// assets/js/activity-map.js picks up every .wc-activity-map container.
$wrapper = get_block_wrapper_attributes(
	array(
		'class'      => 'wc-activity-map',
		'data-lat'   => $lat,
		'data-lng'   => $lng,
		'data-title' => $dest_title,
	)
);

echo '<div ' . $wrapper . '></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
