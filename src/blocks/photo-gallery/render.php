<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/photo-gallery.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_photo_gallery_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
