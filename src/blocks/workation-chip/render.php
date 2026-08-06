<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-chip.
 *
 * @var array $attributes
 */

$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
if ( '' === $title ) {
	return '';
}
$wrapper = get_block_wrapper_attributes( array( 'class' => 'chip' ) );
ob_start();
?>
<span <?php echo $wrapper; ?>><?php echo wp_kses_post( $title ); ?></span>
<?php
echo ob_get_clean();
