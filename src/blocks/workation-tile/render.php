<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-tile.
 *
 * @var array $attributes
 */

$title     = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$image_url = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';

if ( '' === $title && '' === $image_url ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'act' ) );
ob_start();
?>
<article <?php echo $wrapper; ?>>
	<?php if ( '' !== $image_url ) : ?><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>"><?php endif; ?>
	<b><?php echo wp_kses_post( $title ); ?></b>
</article>
<?php
echo ob_get_clean();
