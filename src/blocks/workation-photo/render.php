<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-photo.
 *
 * @var array $attributes
 */

$image_url = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
$variant   = isset( $attributes['variant'] ) ? (string) $attributes['variant'] : '';

if ( '' === $image_url ) {
	return '';
}

$class   = '' !== $variant ? 'g-' . sanitize_html_class( $variant ) : '';
$wrapper = get_block_wrapper_attributes( $class ? array( 'class' => $class ) : array() );
ob_start();
?>
<a <?php echo $wrapper; ?> href="<?php echo esc_url( $image_url ); ?>"><?php echo workation_responsive_image( $image_url, $image_alt, array( 'sizes' => '(max-width: 700px) 45vw, 300px' ) ); ?></a>
<?php
echo ob_get_clean();
