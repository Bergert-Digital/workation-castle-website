<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-tile.
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
	<?php echo workation_responsive_image( $image_url, $image_alt, array( 'sizes' => '(max-width: 700px) 45vw, 25vw' ) ); ?>
	<b><?php echo wp_kses_post( $title ); ?></b>
</article>
<?php
echo ob_get_clean();
