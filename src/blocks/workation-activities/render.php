<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-activities.
 *
 * @package PedimentChild
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_render_workation_section( 'activities', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
