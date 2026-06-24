<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-intro.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_workation_intro_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
