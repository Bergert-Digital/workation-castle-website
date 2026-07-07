<?php
/**
 * Estate map — POI data and section renderer for the /guide/map/ page.
 *
 * @package PedimentChild
 */

/**
 * Points of interest shown on the estate map, in legend order.
 *
 * @return array<int,array<string,mixed>>
 */
function pediment_child_estate_map_pois() {
	return array(
		array(
			'id'     => 'galbiga',
			'marker' => '1',
			'name'   => 'Casa Galbiga',
			'sub'    => 'guest house',
			'type'   => 'place',
			'x'      => 250,
			'y'      => 560,
		),
		array(
			'id'     => 'coworking',
			'marker' => '2',
			'name'   => 'Co-Working Space',
			'sub'    => '',
			'type'   => 'place',
			'x'      => 470,
			'y'      => 520,
		),
		array(
			'id'     => 'bar',
			'marker' => '3',
			'name'   => 'Bar Breva',
			'sub'    => '',
			'type'   => 'place',
			'x'      => 610,
			'y'      => 430,
		),
		array(
			'id'     => 'tremezzo',
			'marker' => '4',
			'name'   => 'Casa Tremezzo',
			'sub'    => 'guest house',
			'type'   => 'place',
			'x'      => 810,
			'y'      => 330,
		),
		array(
			'id'     => 'waste',
			'marker' => '5',
			'name'   => 'Waste Collection Point',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 535,
			'y'      => 360,
		),
		array(
			'id'     => 'parking',
			'marker' => 'P',
			'name'   => 'Parking',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 115,
			'y'      => 280,
		),
		array(
			'id'     => 'garden',
			'marker' => '✿',
			'name'   => 'Courtyard & garden',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 420,
			'y'      => 420,
		),
		array(
			'id'     => 'entrance',
			'marker' => '→',
			'name'   => 'Main entrance',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 30,
			'y'      => 405,
		),
	);
}

/**
 * Static SVG scenery: grounds, paths, courtyard, garden, traced buildings, trees, entrance.
 * Building groups carry class="estate-map__building" data-poi="{id}" so they highlight.
 * NOTE: coordinates are a first-pass trace; refined in the fidelity loop (Task 6).
 *
 * @return string
 */
function pediment_child_estate_map_scenery() {
	return <<<'SVG'
<defs>
	<linearGradient id="wc-roof" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C67E5C"/><stop offset="1" stop-color="#A9583B"/></linearGradient>
	<linearGradient id="wc-roof2" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#BE7150"/><stop offset="1" stop-color="#9E4F35"/></linearGradient>
	<radialGradient id="wc-grass" cx="50%" cy="40%" r="80%"><stop offset="0" stop-color="#9FBC7A"/><stop offset="1" stop-color="#7E9C5C"/></radialGradient>
	<filter id="wc-soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="6" stdDeviation="7" flood-color="#2c1c0e" flood-opacity="0.22"/></filter>
</defs>
<rect width="1200" height="820" fill="url(#wc-grass)"/>
<ellipse cx="250" cy="700" rx="230" ry="120" fill="#A7C382" opacity=".55"/>
<ellipse cx="980" cy="640" rx="230" ry="150" fill="#A7C382" opacity=".5"/>
<ellipse cx="620" cy="120" rx="360" ry="120" fill="#8FAE6A" opacity=".5"/>
<g stroke="#728e50" stroke-width="3" fill="none" opacity=".5"><path d="M120 70 Q600 20 1080 80"/><path d="M90 110 Q600 60 1120 120"/></g>
<g fill="none" stroke="#E8DEC4" stroke-width="16" stroke-linecap="round" opacity=".95"><path d="M40 405 L250 405 Q360 405 430 470 L620 640"/><path d="M430 470 L640 470 Q760 470 820 560"/></g>
<g fill="none" stroke="#c9b98f" stroke-width="16" stroke-linecap="round" stroke-dasharray="2 26" opacity=".8"><path d="M40 405 L250 405 Q360 405 430 470 L620 640"/><path d="M430 470 L640 470 Q760 470 820 560"/></g>
<path class="estate-map__courtyard" d="M300 330 Q470 300 640 330 Q690 430 600 500 Q440 540 320 480 Q280 400 300 330 Z" fill="#EFE7D2" stroke="#D9CBAA" stroke-width="3"/>
<g class="estate-map__building" data-poi="garden">
	<rect x="360" y="360" width="120" height="120" rx="8" fill="#6E9A4E"/>
	<g fill="#8CB86B"><circle cx="390" cy="390" r="7"/><circle cx="430" cy="400" r="7"/><circle cx="410" cy="440" r="7"/><circle cx="455" cy="455" r="7"/><circle cx="380" cy="450" r="7"/></g>
</g>
<g filter="url(#wc-soft)" stroke="#6B3B22" stroke-width="4" stroke-linejoin="round">
	<g class="estate-map__building" data-poi="tremezzo">
		<polygon points="110,235 130,130 600,80 830,120 810,225 590,170 120,205" fill="url(#wc-roof)"/>
		<line x1="130" y1="150" x2="800" y2="200" stroke="#8A4730" stroke-width="3"/>
		<polygon points="810,225 900,180 960,300 870,345 800,300" fill="url(#wc-roof2)"/>
		<polygon points="800,300 870,345 830,420 720,400 700,340" fill="url(#wc-roof)"/>
	</g>
	<g class="estate-map__building" data-poi="bar">
		<polygon points="700,340 720,400 620,470 560,430 600,360" fill="url(#wc-roof2)"/>
	</g>
	<g class="estate-map__building" data-poi="coworking">
		<polygon points="560,430 620,470 520,540 440,510 470,455" fill="url(#wc-roof)"/>
		<polygon points="440,510 520,540 400,590 320,560 340,500" fill="url(#wc-roof2)"/>
	</g>
	<g class="estate-map__building" data-poi="galbiga">
		<polygon points="320,560 340,500 210,520 190,600 300,610" fill="url(#wc-roof)"/>
		<polygon points="120,205 120,410 210,430 210,240" fill="url(#wc-roof2)"/>
		<polygon points="120,410 190,600 210,600 210,430" fill="url(#wc-roof)"/>
		<polygon points="235,285 360,270 380,430 250,445 250,360 235,360" fill="url(#wc-roof2)"/>
	</g>
	<g class="estate-map__building" data-poi="waste">
		<rect x="470" y="300" width="130" height="120" rx="6" fill="url(#wc-roof)"/>
	</g>
	<polygon points="880,420 1000,470 950,570 830,520" fill="url(#wc-roof2)"/>
</g>
<g>
	<g transform="translate(70,560)"><circle r="46" fill="#4f7038"/><circle cx="-16" cy="-14" r="30" fill="#6b9450"/></g>
	<g transform="translate(1040,560)"><circle r="52" fill="#4f7038"/><circle cx="-18" cy="-16" r="34" fill="#6b9450"/></g>
	<g transform="translate(560,700)"><circle r="44" fill="#4f7038"/><circle cx="-14" cy="-14" r="28" fill="#6b9450"/></g>
</g>
<rect x="70" y="250" width="90" height="60" rx="8" fill="#E9E1CE" stroke="#CBBE9E" stroke-width="3" transform="rotate(-6 115 280)"/>
SVG;
}

/**
 * Render the estate-map section (inline SVG + legend), data-driven from the POI list.
 *
 * @param array  $attributes Block attributes (unused; map layout is fixed).
 * @param string $content    Inner blocks (unused).
 * @return string
 */
function pediment_child_estate_map_chrome( $attributes = array(), $content = '' ) {
	$pois    = pediment_child_estate_map_pois();
	$wrapper = get_block_wrapper_attributes( array( 'class' => 'estate-map band' ) );

	$pins = '';
	foreach ( $pois as $poi ) {
		$pins .= sprintf(
			'<g class="estate-map__pin estate-map__pin--%1$s" data-poi="%2$s" transform="translate(%3$d,%4$d)" aria-hidden="true"><circle r="21"></circle><text class="estate-map__pin-num" dy="7" text-anchor="middle">%5$s</text></g>',
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

	$scenery = pediment_child_estate_map_scenery();

	ob_start();
	?>
	<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<div class="wc-wrap estate-map__inner">
			<div class="estate-map__figure">
				<svg class="estate-map__svg" viewBox="0 0 1200 820" role="img" aria-label="<?php echo esc_attr__( 'Illustrated map of Workation Castle showing the guest houses, co-working space, bar and grounds', 'pediment-child' ); ?>">
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
