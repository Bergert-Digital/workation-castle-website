<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-tile.
 *
 * @var array $attributes
 */

$title     = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$link_url  = isset( $attributes['linkUrl'] ) ? (string) $attributes['linkUrl'] : '';
$image_url = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';

if ( '' === $title && '' === $image_url ) {
	return '';
}

$tag       = '' !== $link_url ? 'a' : 'article';
$extra     = '' !== $link_url ? array( 'href' => esc_url( $link_url ) ) : array();
$wrapper   = get_block_wrapper_attributes( array_merge( array( 'class' => 'act' ), $extra ) );
ob_start();
?>
<<?php echo $tag; ?> <?php echo $wrapper; ?>>
	<?php echo workation_responsive_image( $image_url, $image_alt, array( 'sizes' => '(max-width: 700px) 45vw, 25vw' ) ); ?>
	<b><?php echo wp_kses_post( $title ); ?></b>
</<?php echo $tag; ?>>
<?php
echo ob_get_clean();
