<?php
/**
 * Server-side render for workation/check-in-form.
 *
 * Prints a lightweight shell plus a JSON config blob (REST URL, nonce, field
 * definitions, caps, i18n strings from CheckIn::config()) that the view.js
 * wizard reads to build the adaptive multi-step form.
 *
 * @var array $attributes
 */

if ( ! class_exists( '\Workation\CheckIn' ) ) {
	return;
}

$config            = \Workation\CheckIn::config();
$config['restUrl'] = esc_url_raw( rest_url( 'workation/v1/check-in' ) );
$config['nonce']   = wp_create_nonce( 'wp_rest' );

$wrapper = get_block_wrapper_attributes( array( 'class' => 'wc-checkin wc-wrap' ) );
?>
<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<script type="application/json" class="wc-checkin-config">
		<?php echo wp_json_encode( $config, JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</script>
	<div class="wc-checkin-app" data-checkin-app>
		<noscript>
			<p>
				<?php
				esc_html_e(
					'This check-in form needs JavaScript. Please enable it, or email your details to info@workationcastle.com.',
					'workation'
				);
				?>
			</p>
		</noscript>
	</div>
</div>
