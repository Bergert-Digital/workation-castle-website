<?php
/**
 * Workation Castle section block render helpers.
 *
 * @package PedimentChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the reviews section shell around pre-rendered inner blocks.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_reviews_chrome( $attributes, $content ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="reviews">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="wc-wrap">
			<div class="reviews-grid"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the activities section shell around pre-rendered tile blocks.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_activities_chrome( $attributes, $content ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead     = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="activities">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p><?php echo wp_kses_post( $lead ); ?></p>
			<?php endif; ?>
		</div>
		<div class="wc-wrap">
			<div class="act-grid"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the gallery section shell around pre-rendered photo blocks.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_gallery_chrome( $attributes, $content ) {
	$eyebrow      = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline     = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$primary_text = isset( $attributes['primaryText'] ) ? (string) $attributes['primaryText'] : '';
	$primary_url  = isset( $attributes['primaryUrl'] ) ? (string) $attributes['primaryUrl'] : '';
	$wrapper      = get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="gallery">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="wc-wrap">
			<div class="gallery"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php if ( '' !== $primary_text ) : ?>
				<div class="gallery-foot"><a class="wc-btn wc-btn-ghost-dark" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?></a></div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the audience section shell around pre-rendered card blocks.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_audience_chrome( $attributes, $content ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="stay">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="wc-wrap">
			<div class="ways-grid"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Add a class to every even-positioned (0-based 1,3,…) space-row in the
 * pre-rendered inner-block markup, preserving the alternating layout.
 *
 * Note: `reverse` is positional-only — derived at render time by counting
 * space-row occurrences in order — and is never a stored child block attribute.
 *
 * @param string $content Rendered inner blocks.
 * @return string
 */
function pediment_child_workation_mark_reverse_rows( $content ) {
	$index = -1;
	return (string) preg_replace_callback(
		'/class="([^"]*(?<![\w-])space-row(?![\w-])[^"]*)"/',
		function ( $matches ) use ( &$index ) {
			// Skip already-reversed rows so the counter still increments for each
			// genuine space-row but the class is not applied twice (idempotency).
			$already_reversed = (bool) preg_match( '/(?<![\w-])space-row reverse(?! reverse)(?![\w-])/', $matches[1] );
			$index++;
			if ( 1 === $index % 2 && ! $already_reversed ) {
				$new_class = preg_replace( '/(?<![\w-])space-row(?![\w-])/', 'space-row reverse', $matches[1] );
				return 'class="' . $new_class . '"';
			}
			return $matches[0];
		},
		$content
	);
}

/**
 * Render the spaces section shell around pre-rendered space rows.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_spaces_chrome( $attributes, $content ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead     = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$content  = pediment_child_workation_mark_reverse_rows( $content );
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="spaces">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p><?php echo wp_kses_post( $lead ); ?></p>
			<?php endif; ?>
		</div>
		<div class="wc-wrap"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the location section shell around pre-rendered travel modes.
 *
 * @param array  $attributes Block attributes (chrome + map).
 * @param string $content    Pre-rendered inner blocks.
 * @return string
 */
function pediment_child_workation_location_chrome( $attributes, $content ) {
	$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline  = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead      = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$image_url = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
	$wrapper   = get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="location">
		<div class="sec-head">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="wc-wrap">
			<div class="loc-grid">
				<div class="loc-map">
					<?php if ( '' !== $image_url ) : ?>
						<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
					<?php endif; ?>
				</div>
				<div class="loc-text">
					<?php if ( '' !== $lead ) : ?>
						<p><?php echo wp_kses_post( $lead ); ?></p>
					<?php endif; ?>
					<div class="modes"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</div>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the hero section shell around pre-rendered chip blocks.
 *
 * @param array  $attributes Block attributes (chrome).
 * @param string $content    Pre-rendered inner blocks (chips).
 * @return string
 */
function pediment_child_workation_hero_chrome( $attributes, $content ) {
	$eyebrow         = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline        = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead            = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$image_url       = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt       = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
	$primary_text    = isset( $attributes['primaryText'] ) ? (string) $attributes['primaryText'] : '';
	$booking_url     = ! empty( $attributes['bookingUrl'] ) ? (string) $attributes['bookingUrl'] : 'https://workationcastle.holiduhost.com/';
	$check_in_param  = ! empty( $attributes['checkInParam'] ) ? (string) $attributes['checkInParam'] : 'checkIn';
	$check_out_param = ! empty( $attributes['checkOutParam'] ) ? (string) $attributes['checkOutParam'] : 'checkOut';
	$adults_param    = ! empty( $attributes['adultsParam'] ) ? (string) $attributes['adultsParam'] : 'adults';
	$children_param  = ! empty( $attributes['childrenAgesParam'] ) ? (string) $attributes['childrenAgesParam'] : 'childrenAges';
	$secondary_text  = isset( $attributes['secondaryText'] ) ? (string) $attributes['secondaryText'] : '';
	$secondary_url   = isset( $attributes['secondaryUrl'] ) ? (string) $attributes['secondaryUrl'] : '';
	$wrapper         = get_block_wrapper_attributes( array( 'class' => 'hero' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="book">
		<div class="hero-img"><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>"></div>
		<div class="hero-grad"></div>
		<div class="hero-content">
			<div class="wc-wrap">
				<?php if ( '' !== $eyebrow ) : ?>
					<span class="eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $headline ) : ?>
					<h1><?php echo wp_kses_post( $headline ); ?></h1>
				<?php endif; ?>
				<?php if ( '' !== $lead ) : ?>
					<p class="lede"><?php echo wp_kses_post( $lead ); ?></p>
				<?php endif; ?>
				<form class="avail" method="get" action="<?php echo esc_url( $booking_url ); ?>" aria-label="<?php esc_attr_e( 'Check availability', 'pediment-child' ); ?>">
					<div class="avail-field"><label for="arrival"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Arrival', 'pediment-child' ); ?></label><input type="date" id="arrival" name="<?php echo esc_attr( $check_in_param ); ?>"></div>
					<div class="avail-field"><label for="departure"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Departure', 'pediment-child' ); ?></label><input type="date" id="departure" name="<?php echo esc_attr( $check_out_param ); ?>"></div>
					<div class="avail-field select-wrap"><label for="guests"><span class="avail-icon avail-icon--guest" aria-hidden="true"></span> <?php esc_html_e( 'Guests', 'pediment-child' ); ?></label><select id="guests" name="<?php echo esc_attr( $adults_param ); ?>"><option value="2"><?php esc_html_e( '2 guests', 'pediment-child' ); ?></option><option value="1"><?php esc_html_e( '1 guest', 'pediment-child' ); ?></option><option value="3"><?php esc_html_e( '3 guests', 'pediment-child' ); ?></option><option value="4"><?php esc_html_e( '4 guests', 'pediment-child' ); ?></option><option value="5"><?php esc_html_e( '5 guests', 'pediment-child' ); ?></option><option value="6"><?php esc_html_e( '6 guests', 'pediment-child' ); ?></option><option value="7"><?php esc_html_e( '7 guests', 'pediment-child' ); ?></option><option value="8"><?php esc_html_e( '8 guests', 'pediment-child' ); ?></option><option value="9"><?php esc_html_e( '9 guests', 'pediment-child' ); ?></option></select></div>
					<input type="hidden" name="<?php echo esc_attr( $children_param ); ?>" value="">
					<div class="avail-submit"><button type="submit" class="wc-btn wc-btn-yellow"><?php echo esc_html( $primary_text ); ?> <span class="arr" aria-hidden="true">→</span></button></div>
				</form>
				<div class="hero-secondary"><a href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_text ); ?></a></div>
				<div class="hero-chips"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the intro section from attributes.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pediment_child_workation_intro_chrome( $attributes ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead     = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$wrapper  = get_block_wrapper_attributes( array( 'class' => 'band band-cream intro' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<div class="wc-wrap">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p><?php echo wp_kses_post( $lead ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the closing CTA section from attributes.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function pediment_child_workation_closing_chrome( $attributes ) {
	$headline       = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$image_url      = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt      = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
	$primary_text   = isset( $attributes['primaryText'] ) ? (string) $attributes['primaryText'] : '';
	$primary_url    = isset( $attributes['primaryUrl'] ) ? (string) $attributes['primaryUrl'] : '';
	$secondary_text = isset( $attributes['secondaryText'] ) ? (string) $attributes['secondaryText'] : '';
	$secondary_url  = isset( $attributes['secondaryUrl'] ) ? (string) $attributes['secondaryUrl'] : '';
	$link_text      = isset( $attributes['linkText'] ) ? (string) $attributes['linkText'] : '';
	$link_url       = isset( $attributes['linkUrl'] ) ? (string) $attributes['linkUrl'] : '';
	$wrapper        = get_block_wrapper_attributes( array( 'class' => 'closing' ) );
	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<img class="bg" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
		<div class="grad"></div>
		<div class="closing-inner">
			<?php if ( '' !== $headline ) : ?>
				<h2><?php echo wp_kses_post( $headline ); ?></h2>
			<?php endif; ?>
			<div class="actions">
				<?php if ( '' !== $primary_text ) : ?>
					<a class="wc-btn wc-btn-yellow" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?> <span class="arr">→</span></a>
				<?php endif; ?>
				<?php if ( '' !== $secondary_text ) : ?>
					<a class="wc-btn wc-btn-ghost-light" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_text ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $link_text ) : ?>
				<a class="insta" href="<?php echo esc_url( $link_url ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( $link_text ); ?> <span class="arr">→</span></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

