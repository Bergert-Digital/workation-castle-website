<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/page-hero.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_page_hero_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
