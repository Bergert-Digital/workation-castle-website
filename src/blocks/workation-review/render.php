<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-review.
 *
 * @var array $attributes
 */

$text  = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';
$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$role  = isset( $attributes['role'] ) ? (string) $attributes['role'] : '';

if ( '' === $text && '' === $title ) {
	return '';
}

$initial = '' !== $title
	? ( function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 ) )
	: '';
$wrapper = get_block_wrapper_attributes( array( 'class' => 'review' ) );
ob_start();
?>
<article <?php echo $wrapper; ?>>
	<div class="stars">★★★★★</div>
	<p><?php echo wp_kses_post( $text ); ?></p>
	<div class="cite">
		<span class="dot"><?php echo esc_html( $initial ); ?></span>
		<div>
			<b><?php echo wp_kses_post( $title ); ?></b>
			<?php if ( '' !== $role ) : ?>
				<span><?php echo wp_kses_post( $role ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</article>
<?php
echo ob_get_clean();
