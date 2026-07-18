<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/estate-map.
 *
 * @package PedimentChild
 *
 * @var array  $attributes
 * @var string $content
 */

require_once get_theme_file_path( 'inc/EstateMap.php' );

echo pediment_child_estate_map_chrome( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput
