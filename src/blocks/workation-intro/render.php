<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-intro.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_workation_intro_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
