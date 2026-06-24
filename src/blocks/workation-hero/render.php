<?php
// phpcs:ignoreFile
/**
 * Render the Workation hero block.
 *
 * @package PedimentChild
 */

require_once get_theme_file_path( 'inc/WorkationSections.php' );

echo pediment_child_render_workation_section( 'hero', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput
