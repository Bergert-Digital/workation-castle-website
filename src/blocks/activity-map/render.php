<?php
/**
 * @var array    $attributes
 * @var WP_Block $block
 */

$lat        = isset( $attributes['lat'] ) ? (string) $attributes['lat'] : '';
$lng        = isset( $attributes['lng'] ) ? (string) $attributes['lng'] : '';
$dest_title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';

if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
	return '';
}

// Extra markers come from workation/activity-map-pin children.
$pins = array();
foreach ( $block->inner_blocks as $inner ) {
	$pin = $inner->attributes;
	if ( isset( $pin['lat'], $pin['lng'] ) && is_numeric( $pin['lat'] ) && is_numeric( $pin['lng'] ) ) {
		$pins[] = array(
			'lat'   => (float) $pin['lat'],
			'lng'   => (float) $pin['lng'],
			'title' => isset( $pin['title'] ) ? (string) $pin['title'] : '',
		);
	}
}

// assets/js/activity-map.js picks up every .wc-activity-map container.
$wrapper = get_block_wrapper_attributes(
	array(
		'class'      => 'wc-activity-map',
		'data-lat'   => $lat,
		'data-lng'   => $lng,
		'data-title' => $dest_title,
		'data-pins'  => (string) wp_json_encode( $pins ),
	)
);

echo '<div ' . $wrapper . '></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
