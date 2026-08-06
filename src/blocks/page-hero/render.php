<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/page-hero.
 *
 * @var array $attributes
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo workation_page_hero_chrome( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
