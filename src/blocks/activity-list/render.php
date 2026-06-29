<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/activity-list.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_activity_list_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
