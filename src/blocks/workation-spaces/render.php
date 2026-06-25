<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-spaces.
 *
 * @package PedimentChild
 *
 * @var array  $attributes
 * @var string $content
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_workation_spaces_chrome( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput
