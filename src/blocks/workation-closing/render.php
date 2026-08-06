<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-closing.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_workation_closing_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
