<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-location.
 *
 * @package Workation
 *
 * @var array  $attributes
 * @var string $content
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_workation_location_chrome( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput
