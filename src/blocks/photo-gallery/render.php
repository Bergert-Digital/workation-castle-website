<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/photo-gallery.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_photo_gallery_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
