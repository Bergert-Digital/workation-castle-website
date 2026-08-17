<?php
// phpcs:ignoreFile
/**
 * Server-side render for workation/workation-space.
 *
 * @var array $attributes
 */

$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
$title     = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$text      = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';
$link_text = isset( $attributes['linkText'] ) ? (string) $attributes['linkText'] : '';
$link_url  = isset( $attributes['linkUrl'] ) ? (string) $attributes['linkUrl'] : '';
$image_url = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';

if ( '' === $title && '' === $text && '' === $image_url ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'space-row' ) );
ob_start();
?>
<div <?php echo $wrapper; ?>>
	<div class="space-photo"><?php echo workation_responsive_image( $image_url, $image_alt, array( 'sizes' => '(max-width: 700px) 92vw, 50vw' ) ); ?></div>
	<div class="space-text">
		<?php if ( '' !== $eyebrow ) : ?><span class="num"><?php echo wp_kses_post( $eyebrow ); ?></span><?php endif; ?>
		<?php if ( '' !== $title ) : ?><h3><?php echo wp_kses_post( $title ); ?></h3><?php endif; ?>
		<?php if ( '' !== $text ) : ?><p><?php echo wp_kses_post( $text ); ?></p><?php endif; ?>
		<?php if ( '' !== $link_text ) : ?><a href="<?php echo esc_url( $link_url ); ?>" class="text-link"><?php echo wp_kses_post( $link_text ); ?> <span class="arr">→</span></a><?php endif; ?>
	</div>
</div>
<?php
echo ob_get_clean();
