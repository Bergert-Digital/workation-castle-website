<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-mode.
 *
 * @var array $attributes
 */

$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$text  = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';

if ( '' === $title && '' === $text ) {
	return '';
}

$wrapper = get_block_wrapper_attributes( array( 'class' => 'mode' ) );
ob_start();
?>
<div <?php echo $wrapper; ?>>
	<span class="mode-icon" aria-hidden="true"></span>
	<div>
		<b><?php echo wp_kses_post( $title ); ?></b>
		<span><?php echo wp_kses_post( $text ); ?></span>
	</div>
</div>
<?php
echo ob_get_clean();
