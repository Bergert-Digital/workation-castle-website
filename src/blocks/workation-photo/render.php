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
<a <?php echo $wrapper; ?> href="<?php echo esc_url( $image_url ); ?>"><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>"></a>
<?php
echo ob_get_clean();
