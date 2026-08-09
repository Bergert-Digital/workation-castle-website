<?php
/**
 * Estate map — POI data and section renderer for the /guide/map/ page.
 *
 * @package Workation
 */

/**
 * Points of interest shown on the estate map, in legend order.
 *
 * @return array<int,array<string,mixed>>
 */
function workation_estate_map_pois() {
	return array(
		array(
			'id'     => 'galbiga',
			'marker' => '1',
			'name'   => 'Casa Galbiga',
			'sub'    => __( 'guest house', 'workation' ),
			'type'   => 'place',
			'x'      => 360,
			'y'      => 423,
		),
		array(
			'id'     => 'coworking',
			'marker' => '2',
			'name'   => __( 'Workspace', 'workation' ),
			'sub'    => '',
			'type'   => 'place',
			'x'      => 467,
			'y'      => 389,
		),
		array(
			'id'     => 'bar',
			'marker' => '3',
			'name'   => __( 'Bar', 'workation' ),
			'sub'    => __( 'coffee machine and water dispenser', 'workation' ),
			'type'   => 'place',
			'x'      => 485,
			'y'      => 336,
		),
		array(
			'id'     => 'tremezzo',
			'marker' => '4',
			'name'   => 'Casa Tremezzo',
			'sub'    => __( 'guest house', 'workation' ),
			'type'   => 'place',
			'x'      => 524,
			'y'      => 324,
		),
		array(
			'id'     => 'waste',
			'marker' => '5',
			'name'   => __( 'Waste Collection Point', 'workation' ),
			'sub'    => '',
			'type'   => 'service',
			'x'      => 467,
			'y'      => 312,
		),
		array(
			'id'     => 'parking',
			'marker' => 'P',
			'name'   => __( 'Parking', 'workation' ),
			'sub'    => __( 'on Via Castello (on the right side)', 'workation' ),
			'type'   => 'service',
			'x'      => 44,
			'y'      => 318,
		),
	);
}

/**
 * Static SVG scenery for the estate map (viewBox 842x596).
 *
 * The building footprint is traced precisely from the client's source vector
 * plan (each building is a closed polygon recovered from that plan), then styled
 * on-brand. Orientation matches the plan and drone photo: Via Castello and the
 * entrance are on the west (left), guest parking sits on the street's south side,
 * and the planted garden bed is in the main courtyard. Labelled buildings carry
 * class="estate-map__building" data-poi="{id}" so they highlight with the legend.
 *
 * @return string
 */
function workation_estate_map_scenery() {
	return <<<'SVG'
<defs>
<linearGradient id="wc-roof" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C67E5C"/><stop offset="1" stop-color="#A9583B"/></linearGradient>
<radialGradient id="wc-grass" cx="50%" cy="45%" r="80%"><stop offset="0" stop-color="#9FBC7A"/><stop offset="1" stop-color="#7E9C5C"/></radialGradient>
<filter id="wc-soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="3" stdDeviation="3.2" flood-color="#2c1c0e" flood-opacity="0.25"/></filter>
</defs>
<rect width="842" height="596" fill="url(#wc-grass)"/>
<ellipse cx="230" cy="470" rx="210" ry="110" fill="#A7C382" opacity=".45"/>
<ellipse cx="640" cy="470" rx="230" ry="120" fill="#A7C382" opacity=".4"/>
<g fill="#4f7038"><circle cx="60" cy="60" r="58"/><circle cx="180" cy="44" r="64"/><circle cx="340" cy="44" r="60"/><circle cx="520" cy="40" r="64"/><circle cx="680" cy="52" r="60"/><circle cx="790" cy="120" r="60"/><circle cx="800" cy="300" r="54"/><circle cx="770" cy="470" r="60"/><circle cx="560" cy="540" r="58"/><circle cx="300" cy="545" r="56"/><circle cx="40" cy="220" r="48"/><circle cx="40" cy="380" r="48"/><circle cx="120" cy="520" r="50"/></g><g fill="#6b9450"><circle cx="60" cy="46" r="38"/><circle cx="180" cy="30" r="42"/><circle cx="520" cy="28" r="42"/><circle cx="680" cy="38" r="40"/><circle cx="300" cy="530" r="36"/></g>
<g transform="translate(140,430)"><circle r="46" fill="#4f7038"/><circle cx="-15" cy="-13" r="30" fill="#6b9450"/></g>
<path d="M-20 330 Q70 300 150 258" fill="none" stroke="#CFC3A2" stroke-width="30" stroke-linecap="round"/>
<path d="M-20 330 Q70 300 150 258" fill="none" stroke="#E7DCBF" stroke-width="21" stroke-linecap="round"/>
<text x="8" y="316" fill="#5B5042" font-family="Inria Sans, sans-serif" font-size="15" font-style="italic" transform="rotate(-20 8 316)">Via Castello</text>
<path d="M380 210 Q470 195 560 215 Q590 260 560 300 Q470 315 400 295 Q372 255 380 210 Z" fill="#EFE7D2" stroke="#D9CBAA" stroke-width="2" opacity=".9"/>
<g><circle cx="527" cy="243" r="30" fill="#6E9A4E"/><g fill="#8CB86B"><circle cx="516" cy="233" r="4"/><circle cx="537" cy="237" r="4"/><circle cx="524" cy="253" r="4"/><circle cx="540" cy="252" r="4"/><circle cx="510" cy="248" r="4"/></g></g>
<g filter="url(#wc-soft)" stroke="#6B3B22" stroke-width="2.6" stroke-linejoin="round">
<path d="M 121.6 164.8 L 133.6 230.4 L 199.2 220.0 L 189.6 156.8 Z" fill="url(#wc-roof)"/>
<path d="M 252.0 308.8 L 250.4 265.6 L 247.2 252.0 L 138.4 260.0 L 151.2 336.8 L 192.8 330.4 L 192.8 316.0 Z" fill="url(#wc-roof)"/>
<path d="M 252.8 342.4 L 252.0 308.8 L 192.8 316.0 L 192.8 330.4 L 151.2 336.8 L 164.0 478.4 L 287.2 468.8 L 284.8 408.0 L 237.6 412.0 L 231.2 344.8 Z" fill="url(#wc-roof)"/>
<path d="M 267.2 148.0 L 189.6 156.8 L 199.2 220.0 L 272.0 208.0 L 274.4 208.0 Z" fill="url(#wc-roof)"/>
<path d="M 272.0 340.8 L 265.6 305.6 L 332.8 294.4 L 328.0 255.2 L 250.4 265.6 L 252.0 308.8 L 252.8 342.4 Z" fill="url(#wc-roof)"/>
<path d="M 272.0 340.8 L 290.4 339.2 L 296.0 356.8 L 340.0 349.6 L 332.8 294.4 L 265.6 305.6 Z" fill="url(#wc-roof)"/>
<path d="M 287.2 468.8 L 333.6 465.6 L 323.2 404.8 L 284.8 408.0 Z" fill="url(#wc-roof)"/>
<path d="M 274.4 208.0 L 361.6 196.8 L 355.2 137.6 L 267.2 148.0 Z" fill="url(#wc-roof)"/>
<path d="M 355.2 137.6 L 361.6 196.8 L 448.0 186.4 L 448.0 176.0 L 442.4 127.2 Z" fill="url(#wc-roof)"/>
<path d="M 483.2 433.6 L 478.4 428.0 L 465.6 409.6 L 447.2 421.6 L 465.6 448.0 Z" fill="url(#wc-roof)"/>
<path d="M 502.4 384.0 L 496.0 389.6 L 480.8 399.2 L 465.6 409.6 L 478.4 428.0 L 516.0 401.6 Z" fill="url(#wc-roof)"/>
<path d="M 543.2 304.0 L 548.8 312.0 L 560.8 330.4 L 585.6 304.0 L 569.6 280.8 Z" fill="url(#wc-roof)"/>
<path d="M 633.6 152.8 L 585.6 123.2 L 581.6 122.4 L 577.6 169.6 L 612.0 188.0 Z" fill="url(#wc-roof)"/>
<path d="M 601.6 242.4 L 612.0 273.6 L 643.2 242.4 L 659.2 211.2 L 624.0 194.4 Z" fill="url(#wc-roof)"/>
<path d="M 612.0 273.6 L 627.2 298.4 L 663.2 273.6 L 643.2 242.4 Z" fill="url(#wc-roof)"/>
<path d="M 633.6 152.8 L 612.0 188.0 L 624.0 194.4 L 659.2 211.2 L 676.0 179.2 Z" fill="url(#wc-roof)"/>
<path class="estate-map__building" data-poi="galbiga" d="M 380.8 397.6 L 377.6 394.4 L 323.2 404.8 L 333.6 465.6 L 386.4 453.6 Z" fill="url(#wc-roof)"/>
<path class="estate-map__building" data-poi="coworking" d="M 480.8 399.2 L 465.6 379.2 L 480.8 399.2 L 496.0 389.6 L 502.4 384.0 L 525.6 364.0 L 507.2 336.8 L 504.8 338.4 L 478.4 356.8 L 457.6 370.4 L 380.8 397.6 L 386.4 453.6 L 422.4 437.6 L 447.2 421.6 L 465.6 409.6 Z" fill="url(#wc-roof)"/>
<path class="estate-map__building" data-poi="bar" d="M 468.0 332.8 L 478.4 356.8 L 504.8 338.4 L 488.8 316.0 Z" fill="url(#wc-roof)"/>
<path class="estate-map__building" data-poi="tremezzo" d="M 543.2 304.0 L 525.6 284.8 L 484.8 308.8 L 488.8 316.0 L 504.8 338.4 L 507.2 336.8 L 525.6 364.0 L 548.0 344.8 L 560.8 330.4 L 548.8 312.0 Z" fill="url(#wc-roof)"/>
<path d="M 442 127 L 585.6 123.2 L 577.6 169.6 L 448 186 Z" fill="url(#wc-roof)"/>
<path d="M 342 298 L 462 300 L 472 344 L 350 350 Z" fill="url(#wc-roof)"/>
</g>
SVG;
}

/**
 * Render the estate-map section (inline SVG + legend), data-driven from the POI list.
 *
 * @param array  $attributes Block attributes (unused; map layout is fixed).
 * @param string $content    Inner blocks (unused).
 * @return string
 */
function workation_estate_map_chrome( $attributes = array(), $content = '' ) {
	$pois    = workation_estate_map_pois();
	$wrapper = get_block_wrapper_attributes( array( 'class' => 'estate-map band' ) );

	$pins = '';
	foreach ( $pois as $poi ) {
		$pins .= sprintf(
			'<g class="estate-map__pin estate-map__pin--%1$s" data-poi="%2$s" transform="translate(%3$d,%4$d)" aria-hidden="true"><circle r="13"></circle><text class="estate-map__pin-num" dy="5" text-anchor="middle">%5$s</text></g>',
			esc_attr( $poi['type'] ),
			esc_attr( $poi['id'] ),
			(int) $poi['x'],
			(int) $poi['y'],
			esc_html( $poi['marker'] )
		);
	}

	$rows = '';
	foreach ( $pois as $poi ) {
		$sub   = '' !== $poi['sub'] ? ' <span class="estate-map__sub">— ' . esc_html( $poi['sub'] ) . '</span>' : '';
		$rows .= sprintf(
			'<li class="estate-map__item"><button type="button" class="estate-map__row" data-poi="%1$s"><span class="estate-map__marker estate-map__marker--%2$s">%3$s</span><span class="estate-map__name">%4$s%5$s</span></button></li>',
			esc_attr( $poi['id'] ),
			esc_attr( $poi['type'] ),
			esc_html( $poi['marker'] ),
			esc_html( $poi['name'] ),
			$sub // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts above.
		);
	}

	$scenery = workation_estate_map_scenery();

	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<div class="wc-wrap estate-map__inner">
			<div class="estate-map__figure">
				<svg class="estate-map__svg" viewBox="0 0 842 596" role="img" aria-label="<?php echo esc_attr__( 'Illustrated map of Workation Castle showing the guest houses, co-working space, bar and grounds', 'workation' ); ?>">
					<?php echo $scenery; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<g class="estate-map__pins"><?php echo $pins; // phpcs:ignore WordPress.Security.EscapeOutput ?></g>
				</svg>
			</div>
			<ul class="estate-map__legend"><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
