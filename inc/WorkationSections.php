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
	$eyebrow      = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline     = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead         = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$primary_text = isset( $attributes['primaryText'] ) ? (string) $attributes['primaryText'] : '';
	$primary_url  = isset( $attributes['primaryUrl'] ) ? (string) $attributes['primaryUrl'] : '';
	$wrapper      = get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) );
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
			<?php if ( '' !== $primary_text ) : ?>
				<div class="act-foot"><a class="text-link" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?> <span class="arr">→</span></a></div>
			<?php endif; ?>
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
				<div class="gallery-foot"><a class="text-link" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?> <span class="arr">→</span></a></div>
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
	$eyebrow      = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline     = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead         = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$image_url    = isset( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : '';
	$image_alt    = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';
	$primary_text = isset( $attributes['primaryText'] ) ? (string) $attributes['primaryText'] : '';
	$primary_url  = isset( $attributes['primaryUrl'] ) ? (string) $attributes['primaryUrl'] : '';
	$wrapper      = get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) );
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
					<?php if ( '' !== $primary_text ) : ?>
						<a class="text-link loc-cta" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( $primary_text ); ?> <span class="arr">→</span></a>
					<?php endif; ?>
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
				<?php
				echo pediment_child_render_availability_form( // phpcs:ignore WordPress.Security.EscapeOutput
					array(
						'booking_url'     => $booking_url,
						'check_in_param'  => $check_in_param,
						'check_out_param' => $check_out_param,
						'adults_param'    => $adults_param,
						'children_param'  => $children_param,
						'submit_text'     => $primary_text,
					)
				);
				?>
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
 * Render the filterable photo gallery from the wc_photo CPT.
 *
 * Queries every published photo ordered by menu_order and outputs a tab bar
 * (All + each category in use) plus a grid of lightbox-able anchors. The
 * photo image comes from each post's featured image.
 *
 * @param array $attributes Block attributes (eyebrow, headline).
 * @return string
 */
function pediment_child_photo_gallery_chrome( $attributes ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';

	$query = new WP_Query(
		array(
			'post_type'      => PEDIMENT_CHILD_PHOTO_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$used_terms = array();
	$cards      = array();

	foreach ( $query->posts as $post ) {
		$full = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
		if ( ! $full ) {
			continue;
		}
		$terms     = get_the_terms( $post->ID, PEDIMENT_CHILD_PHOTO_TAX );
		$slugs     = array();
		$cat_label = '';
		if ( is_array( $terms ) ) {
			foreach ( $terms as $term ) {
				$slugs[]                   = $term->slug;
				$used_terms[ $term->slug ] = $term->name;
			}
			if ( ! empty( $terms ) ) {
				$cat_label = reset( $terms )->name;
			}
		}
		$title = get_the_title( $post->ID );
		$img   = wp_get_attachment_image(
			get_post_thumbnail_id( $post->ID ),
			'large',
			false,
			array(
				'alt'     => $title,
				'loading' => 'lazy',
			)
		);

		// Caption overlay: category + description. Skip the description when it
		// merely repeats the category (some photos are titled after their room).
		$meta = '';
		if ( '' !== $cat_label ) {
			$meta .= '<span class="photo-cat">' . esc_html( $cat_label ) . '</span>';
		}
		if ( '' !== $title && strtolower( $title ) !== strtolower( $cat_label ) ) {
			$meta .= '<span class="photo-title">' . esc_html( $title ) . '</span>';
		}
		if ( '' !== $meta ) {
			$meta = '<span class="photo-meta">' . $meta . '</span>';
		}

		$cards[] = sprintf(
			'<a class="photo" href="%1$s" data-category="%2$s">%3$s%4$s</a>',
			esc_url( $full ),
			esc_attr( implode( ' ', $slugs ) ),
			$img,
			$meta
		);
	}

	wp_reset_postdata();

	$align       = isset( $attributes['align'] ) ? (string) $attributes['align'] : '';
	$align_class = '' !== $align ? ' align' . $align : '';
	ob_start();
	?>
	<section class="band photos<?php echo esc_attr( $align_class ); ?>" id="photos">
		<?php if ( '' !== $eyebrow || '' !== $headline ) : ?>
			<div class="sec-head">
				<?php if ( '' !== $eyebrow ) : ?>
					<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $headline ) : ?>
					<h2><?php echo wp_kses_post( $headline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="wc-wrap">
			<div class="photo-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter photos', 'pediment-child' ); ?>">
				<button type="button" class="photo-tab is-active" data-filter="*" aria-pressed="true"><?php esc_html_e( 'All', 'pediment-child' ); ?></button>
				<?php foreach ( $used_terms as $slug => $name ) : ?>
					<button type="button" class="photo-tab" data-filter="<?php echo esc_attr( $slug ); ?>" aria-pressed="false"><?php echo esc_html( $name ); ?></button>
				<?php endforeach; ?>
			</div>
			<div class="photo-grid">
				<?php echo implode( "\n", $cards ); // phpcs:ignore WordPress.Security.EscapeOutput -- anchors built from escaped parts above. ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the activities grid: one linked card per published wc_activity.
 *
 * @param array $attributes Block attributes (eyebrow, headline, align).
 * @return string
 */
function pediment_child_activity_list_chrome( $attributes ) {
	$eyebrow  = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';

	$query = new WP_Query(
		array(
			'post_type'      => PEDIMENT_CHILD_ACTIVITY_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		)
	);

	$cards = array();
	foreach ( $query->posts as $post ) {
		$title   = get_the_title( $post->ID );
		$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
		$img     = '';
		$thumb   = get_post_thumbnail_id( $post->ID );
		if ( $thumb ) {
			$img = wp_get_attachment_image(
				$thumb,
				'medium_large',
				false,
				array(
					'alt'     => $title,
					'loading' => 'lazy',
				)
			);
		}

		$cards[] = sprintf(
			'<a class="activity-card" href="%1$s"><span class="activity-card__media">%2$s</span><span class="activity-card__body"><span class="activity-card__title">%3$s</span>%4$s</span></a>',
			esc_url( get_permalink( $post->ID ) ),
			$img,
			esc_html( $title ),
			'' !== $excerpt ? '<span class="activity-card__text">' . esc_html( $excerpt ) . '</span>' : ''
		);
	}

	wp_reset_postdata();

	$align       = isset( $attributes['align'] ) ? (string) $attributes['align'] : '';
	$align_class = '' !== $align ? ' align' . $align : '';
	ob_start();
	?>
	<section class="band band-cream activities<?php echo esc_attr( $align_class ); ?>" id="activities">
		<?php if ( '' !== $eyebrow || '' !== $headline ) : ?>
			<div class="sec-head">
				<?php if ( '' !== $eyebrow ) : ?>
					<span class="wc-kicker"><?php echo wp_kses_post( $eyebrow ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== $headline ) : ?>
					<h2><?php echo wp_kses_post( $headline ); ?></h2>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="wc-wrap">
			<div class="activity-grid">
				<?php echo implode( "\n", $cards ); // phpcs:ignore WordPress.Security.EscapeOutput -- cards built from escaped parts above. ?>
			</div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}

/**
 * Default page-hero background — the homepage hero image. Mirrors the
 * workation-hero block.json default so interior heroes share the homepage's
 * cinematic photo unless a page overrides it.
 */
const PEDIMENT_CHILD_PAGE_HERO_DEFAULT_IMAGE = 'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Piano_Lake.jpg';

/**
 * Render the interior-page hero from attributes.
 *
 * A reusable cinematic hero for non-home pages: a full-bleed photo with the
 * same gradient family as the homepage hero, headline anchored bottom-left.
 * When no image is set it falls back to the homepage hero image, so the header
 * always overlays a dark photo (transparent/white, like the homepage).
 *
 * @param array $attributes Block attributes (eyebrow, headline, lead, imageUrl,
 *                          imageAlt).
 * @return string
 */
function pediment_child_page_hero_chrome( $attributes ) {
	$eyebrow   = isset( $attributes['eyebrow'] ) ? (string) $attributes['eyebrow'] : '';
	$headline  = isset( $attributes['headline'] ) ? (string) $attributes['headline'] : '';
	$lead      = isset( $attributes['lead'] ) ? (string) $attributes['lead'] : '';
	$image_url = ! empty( $attributes['imageUrl'] ) ? (string) $attributes['imageUrl'] : PEDIMENT_CHILD_PAGE_HERO_DEFAULT_IMAGE;
	$image_alt = isset( $attributes['imageAlt'] ) ? (string) $attributes['imageAlt'] : '';

	$align       = isset( $attributes['align'] ) ? (string) $attributes['align'] : '';
	$align_class = '' !== $align ? ' align' . $align : '';
	ob_start();
	?>
	<section class="page-hero<?php echo esc_attr( $align_class ); ?>">
		<div class="page-hero-img"><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>"></div>
		<div class="page-hero-grad"></div>
		<div class="page-hero-inner wc-wrap">
			<?php if ( '' !== $eyebrow ) : ?>
				<span class="eyebrow"><?php echo wp_kses_post( $eyebrow ); ?></span>
			<?php endif; ?>
			<?php if ( '' !== $headline ) : ?>
				<h1><?php echo wp_kses_post( $headline ); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $lead ) : ?>
				<p class="page-hero-lede"><?php echo wp_kses_post( $lead ); ?></p>
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
					<a class="text-link" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( $secondary_text ); ?> <span class="arr">→</span></a>
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

