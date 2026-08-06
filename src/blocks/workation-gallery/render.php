<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-gallery.
 *
 * @var array  $attributes
 * @var string $content
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_workation_gallery_chrome( $attributes, $content ); // phpcs:ignore WordPress.Security.EscapeOutput
