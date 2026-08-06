<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/estate-map.
 *
 * @package Workation
 *
 * @var array  $attributes
 * @var string $content
 */

require_once get_theme_file_path( 'inc/EstateMap.php' );

echo workation_estate_map_chrome( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput
