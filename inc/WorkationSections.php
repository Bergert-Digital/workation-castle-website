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
 * Return default content for Workation Castle section blocks.
 *
 * @param string $section Section key.
 * @return array<string,mixed>
 */
function pediment_child_workation_section_defaults( $section ) {
	$defaults = array(
		'hero'       => array(
			'eyebrow'       => 'Castello di Carlazzo · Northern Italy',
			'headline'      => 'An Italian castle to <span class="hl">work, gather</span> and unwind.',
			'lead'          => "Centuries-old walls and bright, modern interiors on a hill between Lake Como and Lake Lugano — with a full co-working space built for teams who'd rather work somewhere extraordinary.",
			'imageUrl'      => 'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Piano_Lake.jpg',
			'imageAlt'      => 'Aerial view of the castle hamlet above Lago di Piano with the mountains beyond',
			'primaryText'   => 'Check availability',
			'primaryUrl'    => '#book',
			'secondaryText' => 'Ask for a custom offer',
			'secondaryUrl'  => '#book',
			'items'         => array(
				array( 'title' => 'Up to 9 guests' ),
				array( 'title' => '2 vacation homes' ),
				array( 'title' => '5 bedrooms' ),
				array( 'title' => 'Co-working space' ),
				array( 'title' => 'By a nature reserve' ),
				array( 'title' => '~1h from Milan Malpensa' ),
			),
		),
		'intro'      => array(
			'eyebrow'  => 'Why here',
			'headline' => 'Centuries-old walls, bright modern interiors',
			'lead'     => 'Set on a small hill beside the Lago di Piano nature reserve, Workation Castle pairs historic stone with light, comfortable rooms. Come to focus, to bring your team together, or simply to slow down — with lakes, mountains and quiet walks right outside the door.',
		),
		'audience'   => array(
			'eyebrow'  => 'Three ways to stay',
			'headline' => 'One castle, made for the way you want to be together.',
			'items'    => array(
				array(
					'eyebrow'  => '01 — Team retreats',
					'title'    => 'Team retreats',
					'text'     => 'Meeting rooms, focus spaces and beds for the whole team — work, eat and stay together in one place.',
					'linkText' => 'Plan a retreat',
					'linkUrl'  => '#book',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2186.jpeg',
					'imageAlt' => 'Meeting room with a view over the landscape',
				),
				array(
					'eyebrow'  => '02 — Workations',
					'title'    => 'Workations',
					'text'     => 'Fast Wi-Fi, calm rooms and a view that makes a Monday feel completely different.',
					'linkText' => 'See the workspace',
					'linkUrl'  => '#spaces',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_1758.jpeg',
					'imageAlt' => 'Vaulted co-working room lit with warm string lights',
				),
				array(
					'eyebrow'  => '03 — Family & groups',
					'title'    => 'Family & group stays',
					'text'     => 'Two homes, five bedrooms, gardens and a swimmable lake within walking distance.',
					'linkText' => 'Explore the homes',
					'linkUrl'  => '#book',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2263.jpeg',
					'imageAlt' => 'Living room with a bright yellow armchair',
				),
			),
		),
		'spaces'     => array(
			'eyebrow'  => 'The spaces',
			'headline' => 'Room to work, and room to stay.',
			'lead'     => 'Original stone and vaulted ceilings, paired with modern, comfortable interiors — historic character without the heaviness.',
			'items'    => array(
				array(
					'eyebrow'  => '01',
					'title'    => 'The workspace',
					'text'     => 'Two rooms for focused work, a large meeting room, a phone booth, a small lounge and a community kitchen. Versatile enough for a coaching retreat — or for finishing your thesis in the quiet.',
					'linkText' => 'See the workspace',
					'linkUrl'  => '#book',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_1758.jpeg',
					'imageAlt' => 'The co-working space — a vaulted room lit with warm string lights',
				),
				array(
					'eyebrow'  => '02',
					'title'    => 'Two vacation homes',
					'text'     => 'Two separately bookable houses with five bedrooms for up to nine guests, each with access to a garden. Modern comfort tucked inside centuries-old walls.',
					'linkText' => 'Explore the homes',
					'linkUrl'  => '#book',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2263.jpeg',
					'imageAlt' => 'Living room of one of the vacation homes with a bright yellow armchair',
				),
			),
		),
		'location'   => array(
			'eyebrow'  => 'Location & getting here',
			'headline' => 'Between two lakes, near the Swiss border.',
			'lead'     => 'In an Italian valley near the Swiss border, between Lake Lugano and Lake Como — with the little Lago di Piano a short walk away. Restaurants, a bakery and shops are within walking distance; most guests leave the car parked all week.',
			'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2022/12/Castello-Map-Screenshot.png',
			'imageAlt' => "Map showing the castle's location between Lake Lugano and Lake Como",
			'items'    => array(
				array(
					'icon'  => 'car',
					'title' => 'By car',
					'text'  => 'Free parking on site',
				),
				array(
					'icon'  => 'train',
					'title' => 'By train',
					'text'  => 'Via Lugano',
				),
				array(
					'icon'  => 'plane',
					'title' => 'By plane',
					'text'  => 'Via Milan Malpensa',
				),
			),
		),
		'activities' => array(
			'eyebrow'  => 'When laptops close',
			'headline' => 'Lakes, trails and mountain air are part of the stay.',
			'lead'     => 'Swim before dinner, walk the nature reserve, chase waterfalls or head into the mountains — without turning the trip into logistics.',
			'items'    => array(
				array(
					'title'    => 'Swim in the lake',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2022/09/IMG_0531-scaled.jpeg',
					'imageAlt' => 'Clear lake water near the castle',
				),
				array(
					'title'    => 'Forest trails',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5944.jpeg',
					'imageAlt' => 'Forest path near Lago di Piano',
				),
				array(
					'title'    => 'Waterfalls',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/09/IMG_5947.jpeg',
					'imageAlt' => 'Waterfall near the castle',
				),
				array(
					'title'    => 'Mountain views',
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/05/IMG_1511.jpeg',
					'imageAlt' => 'Mountain view above Lake Como',
				),
			),
		),
		'gallery'    => array(
			'eyebrow'     => 'Inside the castle',
			'headline'    => 'Old stone, warm rooms and space to breathe.',
			'primaryText' => 'See more photos',
			'primaryUrl'  => '#book',
			'items'       => array(
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
					'imageAlt' => 'Terrace and garden view from the castle',
					'variant'  => 'tall',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Lamp.jpeg',
					'imageAlt' => 'Warm interior detail with lamp',
					'variant'  => 'wide',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2152.jpeg',
					'imageAlt' => 'Bright bedroom inside the castle',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2265.jpeg',
					'imageAlt' => 'Kitchen and dining area',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2247.jpeg',
					'imageAlt' => 'Castle room with modern furnishings',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2113.jpeg',
					'imageAlt' => 'Stone stairway inside the castle',
				),
				array(
					'imageUrl' => 'https://workationcastle.com/wp-content/uploads/2024/01/Workation_Castle_Roofs.jpeg',
					'imageAlt' => 'Castle roofs and surrounding hills',
					'variant'  => 'wide',
				),
			),
		),
		'reviews'    => array(
			'eyebrow'  => 'Guest reviews',
			'headline' => 'People come for focus, family time and the quiet.',
			'items'    => array(
				array(
					'title' => 'Alexander M.',
					'role'  => 'Workation guest',
					'text'  => 'The location is perfect — right between Lake Como and Lake Lugano, with a bonus small lake five minutes from the location. The co-working space exceeds expectations.',
				),
				array(
					'title' => 'Simone S.',
					'role'  => 'Workation stay',
					'text'  => 'Ein toller und sehr entspannter Ort zum Arbeiten oder Urlaub machen. Das Haus und die gemeinsamen Arbeitsräume sind super ausgestattet.',
				),
				array(
					'title' => 'Manuelle B.',
					'role'  => 'Group stay',
					'text'  => 'Die Atmosphäre des alten Gemäuers, den Ausblick von der Terrasse. Tolles und durchdachtes Konzept, auch für größere Familien, Gruppen und zum Arbeiten.',
				),
				array(
					'title' => 'Corinne O.',
					'role'  => 'Castle guest',
					'text'  => 'Castello di Carlazzo — wunderschönes Kleinod in ursprünglicher Substanz und sehr ansprechend renovierten Gebäudeteilen.',
				),
				array(
					'title' => 'Kathrin K.',
					'role'  => 'Workation guest',
					'text'  => 'Das Castello ist wirklich der perfekte Ort für Workation. Die Büroräume sind großzügig und sehr geschmackvoll eingerichtet.',
				),
				array(
					'title' => 'Philippe R.',
					'role'  => 'Family stay',
					'text'  => 'The terrace, quietness, view on the mountains and the animals on the plain below. The host was very accommodating.',
				),
			),
		),
		'closing'    => array(
			'headline'      => 'Bring your next week of work somewhere memorable.',
			'imageUrl'      => 'https://workationcastle.com/wp-content/uploads/2023/08/IMG_2019.jpeg',
			'imageAlt'      => 'Garden terrace at Workation Castle',
			'primaryText'   => 'Check availability',
			'primaryUrl'    => '#book',
			'secondaryText' => 'Ask for a custom offer',
			'secondaryUrl'  => '#book',
			'linkText'      => 'Follow on Instagram',
			'linkUrl'       => 'https://www.instagram.com/workationcastle/',
		),
	);

	return isset( $defaults[ $section ] ) ? $defaults[ $section ] : array();
}

/**
 * Merge attributes with defaults for a Workation Castle section.
 *
 * @param string $section    Section key.
 * @param array  $attributes Block attributes.
 * @return array<string,mixed>
 */
function pediment_child_workation_section_data( $section, $attributes ) {
	$defaults = pediment_child_workation_section_defaults( $section );
	$data     = $defaults;

	if ( is_array( $attributes ) ) {
		foreach ( $attributes as $key => $value ) {
			if ( 'items' === $key ) {
				continue;
			}

			if ( '' !== $value && null !== $value ) {
				$data[ $key ] = $value;
			}
		}
	}

	if ( isset( $attributes['items'] ) && is_array( $attributes['items'] ) && ! empty( $attributes['items'] ) ) {
		$data['items'] = $attributes['items'];
	} elseif ( empty( $data['items'] ) && ! empty( $defaults['items'] ) ) {
		$data['items'] = $defaults['items'];
	}

	return $data;
}

/**
 * Return sanitized item value.
 *
 * @param array  $item Item data.
 * @param string $key  Item key.
 * @return string
 */
function pediment_child_workation_item_value( $item, $key ) {
	return isset( $item[ $key ] ) ? (string) $item[ $key ] : '';
}

/**
 * Render the shared travel mode icon.
 *
 * @param string $icon Icon key.
 * @return string
 */
function pediment_child_workation_mode_icon( $icon ) {
	if ( 'train' === $icon ) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 4h10a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V6a2 2 0 0 1 2-2Z"/><path d="M8 9h8M8 14h8M9 21l2-3M15 18l2 3"/></svg>';
	}

	if ( 'plane' === $icon ) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10 21l2-8L4 9l1-2 8 2 4-6 2 1-3 7 5 3-1.5 1.5-5-1-2.5 7Z"/></svg>';
	}

	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 16h14M7 16l1-6h8l1 6M8 19h.01M16 19h.01M6 13h12"/></svg>';
}

/**
 * Render a Workation Castle section block.
 *
 * @param string $section    Section key.
 * @param array  $attributes Block attributes.
 * @return string
 */
function pediment_child_render_workation_section( $section, $attributes ) {
	$data  = pediment_child_workation_section_data( $section, $attributes );
	$items = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : array();

	ob_start();
	if ( 'hero' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'hero' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="book">
			<div class="hero-img"><img src="<?php echo esc_url( (string) $data['imageUrl'] ); ?>" alt="<?php echo esc_attr( (string) $data['imageAlt'] ); ?>"></div>
			<div class="hero-grad"></div>
			<div class="hero-content">
				<div class="wc-wrap">
					<span class="eyebrow"><?php echo wp_kses_post( (string) $data['eyebrow'] ); ?></span>
					<h1><?php echo wp_kses_post( (string) $data['headline'] ); ?></h1>
					<p class="lede"><?php echo wp_kses_post( (string) $data['lead'] ); ?></p>
					<form class="avail" action="<?php echo esc_url( (string) $data['primaryUrl'] ); ?>" aria-label="<?php esc_attr_e( 'Check availability', 'pediment-child' ); ?>">
						<div class="avail-field"><label for="arrival"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Arrival', 'pediment-child' ); ?></label><input type="date" id="arrival" name="arrival"></div>
						<div class="avail-field"><label for="departure"><span class="avail-icon" aria-hidden="true"></span> <?php esc_html_e( 'Departure', 'pediment-child' ); ?></label><input type="date" id="departure" name="departure"></div>
						<div class="avail-field select-wrap"><label for="guests"><span class="avail-icon avail-icon--guest" aria-hidden="true"></span> <?php esc_html_e( 'Guests', 'pediment-child' ); ?></label><select id="guests" name="guests"><option value="2"><?php esc_html_e( '2 guests', 'pediment-child' ); ?></option><option value="1"><?php esc_html_e( '1 guest', 'pediment-child' ); ?></option><option value="3"><?php esc_html_e( '3 guests', 'pediment-child' ); ?></option><option value="4"><?php esc_html_e( '4 guests', 'pediment-child' ); ?></option><option value="5"><?php esc_html_e( '5 guests', 'pediment-child' ); ?></option><option value="6"><?php esc_html_e( '6 guests', 'pediment-child' ); ?></option><option value="7"><?php esc_html_e( '7 guests', 'pediment-child' ); ?></option><option value="8"><?php esc_html_e( '8 guests', 'pediment-child' ); ?></option><option value="9"><?php esc_html_e( '9 guests', 'pediment-child' ); ?></option></select></div>
						<div class="avail-submit"><button type="submit" class="wc-btn wc-btn-yellow"><?php echo esc_html( (string) $data['primaryText'] ); ?> <span class="arr" aria-hidden="true">→</span></button></div>
					</form>
					<div class="hero-secondary"><a href="<?php echo esc_url( (string) $data['secondaryUrl'] ); ?>"><?php echo esc_html( (string) $data['secondaryText'] ); ?></a></div>
					<div class="hero-chips">
						<?php foreach ( $items as $item ) : ?>
							<span class="chip"><?php echo esc_html( pediment_child_workation_item_value( $item, 'title' ) ); ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	elseif ( 'intro' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-cream intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<div class="wc-wrap">
				<span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span>
				<h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2>
				<p><?php echo wp_kses_post( (string) $data['lead'] ); ?></p>
			</div>
		</section>
		<?php
	elseif ( in_array( $section, array( 'audience', 'spaces', 'location', 'activities', 'gallery', 'reviews', 'closing' ), true ) ) :
		echo pediment_child_render_workation_section_body( $section, $data, $items ); // phpcs:ignore WordPress.Security.EscapeOutput
	endif;

	return (string) ob_get_clean();
}

/**
 * Render the larger section bodies.
 *
 * @param string $section Section key.
 * @param array  $data    Section data.
 * @param array  $items   Item data.
 * @return string
 */
function pediment_child_render_workation_section_body( $section, $data, $items ) {
	ob_start();
	if ( 'audience' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="stay">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2></div>
			<div class="wc-wrap"><div class="ways-grid">
				<?php foreach ( $items as $item ) : ?>
					<article class="way">
						<img src="<?php echo esc_url( pediment_child_workation_item_value( $item, 'imageUrl' ) ); ?>" alt="<?php echo esc_attr( pediment_child_workation_item_value( $item, 'imageAlt' ) ); ?>">
						<div class="way-body"><span class="way-num"><?php echo esc_html( pediment_child_workation_item_value( $item, 'eyebrow' ) ); ?></span><h3><?php echo wp_kses_post( pediment_child_workation_item_value( $item, 'title' ) ); ?></h3><p><?php echo wp_kses_post( pediment_child_workation_item_value( $item, 'text' ) ); ?></p><a class="link" href="<?php echo esc_url( pediment_child_workation_item_value( $item, 'linkUrl' ) ); ?>"><?php echo esc_html( pediment_child_workation_item_value( $item, 'linkText' ) ); ?> <span class="arr">→</span></a></div>
					</article>
				<?php endforeach; ?>
			</div></div>
		</section>
		<?php
	elseif ( 'spaces' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="spaces">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2><p><?php echo wp_kses_post( (string) $data['lead'] ); ?></p></div>
			<div class="wc-wrap">
				<?php foreach ( $items as $index => $item ) : ?>
					<div class="space-row<?php echo 1 === $index ? ' reverse' : ''; ?>"><div class="space-photo"><img src="<?php echo esc_url( pediment_child_workation_item_value( $item, 'imageUrl' ) ); ?>" alt="<?php echo esc_attr( pediment_child_workation_item_value( $item, 'imageAlt' ) ); ?>"></div><div class="space-text"><span class="num"><?php echo esc_html( pediment_child_workation_item_value( $item, 'eyebrow' ) ); ?></span><h3><?php echo wp_kses_post( pediment_child_workation_item_value( $item, 'title' ) ); ?></h3><p><?php echo wp_kses_post( pediment_child_workation_item_value( $item, 'text' ) ); ?></p><a href="<?php echo esc_url( pediment_child_workation_item_value( $item, 'linkUrl' ) ); ?>" class="text-link"><?php echo esc_html( pediment_child_workation_item_value( $item, 'linkText' ) ); ?> <span class="arr">→</span></a></div></div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	elseif ( 'location' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="location">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2></div>
			<div class="wc-wrap"><div class="loc-grid"><div class="loc-map"><img src="<?php echo esc_url( (string) $data['imageUrl'] ); ?>" alt="<?php echo esc_attr( (string) $data['imageAlt'] ); ?>"></div><div class="loc-text"><p><?php echo wp_kses_post( (string) $data['lead'] ); ?></p><div class="modes">
				<?php foreach ( $items as $item ) : ?>
					<div class="mode"><span class="mode-icon" aria-hidden="true"><?php echo pediment_child_workation_mode_icon( pediment_child_workation_item_value( $item, 'icon' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><div><b><?php echo esc_html( pediment_child_workation_item_value( $item, 'title' ) ); ?></b><span><?php echo esc_html( pediment_child_workation_item_value( $item, 'text' ) ); ?></span></div></div>
				<?php endforeach; ?>
			</div></div></div></div>
		</section>
		<?php
	elseif ( 'activities' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="activities">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2><p><?php echo wp_kses_post( (string) $data['lead'] ); ?></p></div>
			<div class="wc-wrap"><div class="act-grid">
				<?php foreach ( $items as $item ) : ?>
					<article class="act"><img src="<?php echo esc_url( pediment_child_workation_item_value( $item, 'imageUrl' ) ); ?>" alt="<?php echo esc_attr( pediment_child_workation_item_value( $item, 'imageAlt' ) ); ?>"><b><?php echo esc_html( pediment_child_workation_item_value( $item, 'title' ) ); ?></b></article>
				<?php endforeach; ?>
			</div></div>
		</section>
		<?php
	elseif ( 'gallery' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-deep' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="gallery">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2></div>
			<div class="wc-wrap"><div class="gallery">
				<?php foreach ( $items as $item ) : ?>
					<a class="<?php echo esc_attr( 'g-' . pediment_child_workation_item_value( $item, 'variant' ) ); ?>" href="<?php echo esc_url( pediment_child_workation_item_value( $item, 'imageUrl' ) ); ?>"><img src="<?php echo esc_url( pediment_child_workation_item_value( $item, 'imageUrl' ) ); ?>" alt="<?php echo esc_attr( pediment_child_workation_item_value( $item, 'imageAlt' ) ); ?>"></a>
				<?php endforeach; ?>
			</div><div class="gallery-foot"><a href="<?php echo esc_url( (string) $data['primaryUrl'] ); ?>" class="wc-btn wc-btn-ghost-dark"><?php echo esc_html( (string) $data['primaryText'] ); ?> <span class="arr">→</span></a></div></div>
		</section>
		<?php
	elseif ( 'reviews' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'band band-cream' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?> id="reviews">
			<div class="sec-head"><span class="wc-kicker"><?php echo esc_html( (string) $data['eyebrow'] ); ?></span><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2></div>
			<div class="wc-wrap"><div class="reviews-grid">
				<?php foreach ( $items as $item ) : ?>
					<article class="review"><div class="stars" aria-label="<?php esc_attr_e( 'Five stars', 'pediment-child' ); ?>">★★★★★</div><p>"<?php echo wp_kses_post( pediment_child_workation_item_value( $item, 'text' ) ); ?>"</p><div class="cite"><span class="dot"><?php echo esc_html( strtoupper( substr( pediment_child_workation_item_value( $item, 'title' ), 0, 1 ) ) ); ?></span><div><b><?php echo esc_html( pediment_child_workation_item_value( $item, 'title' ) ); ?></b><span><?php echo esc_html( pediment_child_workation_item_value( $item, 'role' ) ); ?></span></div></div></article>
				<?php endforeach; ?>
			</div></div>
		</section>
		<?php
	elseif ( 'closing' === $section ) :
		?>
		<section <?php echo get_block_wrapper_attributes( array( 'class' => 'closing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<img class="bg" src="<?php echo esc_url( (string) $data['imageUrl'] ); ?>" alt="<?php echo esc_attr( (string) $data['imageAlt'] ); ?>"><div class="grad"></div><div class="closing-inner"><h2><?php echo wp_kses_post( (string) $data['headline'] ); ?></h2><div class="actions"><a href="<?php echo esc_url( (string) $data['primaryUrl'] ); ?>" class="wc-btn wc-btn-yellow"><?php echo esc_html( (string) $data['primaryText'] ); ?> <span class="arr">→</span></a><a href="<?php echo esc_url( (string) $data['secondaryUrl'] ); ?>" class="wc-btn wc-btn-ghost-light"><?php echo esc_html( (string) $data['secondaryText'] ); ?></a></div><a class="insta" href="<?php echo esc_url( (string) $data['linkUrl'] ); ?>" target="_blank" rel="noreferrer"><?php echo esc_html( (string) $data['linkText'] ); ?> <span class="arr">→</span></a></div>
		</section>
		<?php
	endif;

	return (string) ob_get_clean();
}
