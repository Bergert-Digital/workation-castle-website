<?php
// phpcs:ignoreFile
/**
 * Server-side render for pediment-child/workation-mode.
 *
 * @var array $attributes
 */

$title = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$text  = isset( $attributes['text'] ) ? (string) $attributes['text'] : '';
$icon  = isset( $attributes['icon'] ) ? (string) $attributes['icon'] : 'car';

if ( '' === $title && '' === $text ) {
	return '';
}

$svg_open  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
$svg_close = '</svg>';
$icons     = array(
	'car'   => $svg_open . '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>' . $svg_close,
	'train' => $svg_open . '<rect width="16" height="16" x="4" y="3" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="m8 19-2 3"/><path d="m18 22-2-3"/><path d="M8 15h.01"/><path d="M16 15h.01"/>' . $svg_close,
	'plane' => $svg_open . '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>' . $svg_close,
);
$icon_svg = isset( $icons[ $icon ] ) ? $icons[ $icon ] : $icons['car'];

$wrapper = get_block_wrapper_attributes( array( 'class' => 'mode' ) );
ob_start();
?>
<div <?php echo $wrapper; ?>>
	<span class="mode-icon" aria-hidden="true"><?php echo $icon_svg; ?></span>
	<div>
		<b><?php echo wp_kses_post( $title ); ?></b>
		<span><?php echo wp_kses_post( $text ); ?></span>
	</div>
</div>
<?php
echo ob_get_clean();
