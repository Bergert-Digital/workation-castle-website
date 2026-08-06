<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/activity-list.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_activity_list_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
