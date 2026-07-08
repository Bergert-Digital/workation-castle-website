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
			'x'      => 360,
			'y'      => 700,
		),
		array(
			'id'     => 'coworking',
			'marker' => '2',
			'name'   => 'Co-Working Space',
			'sub'    => '',
			'type'   => 'place',
			'x'      => 730,
			'y'      => 812,
		),
		array(
			'id'     => 'bar',
			'marker' => '3',
			'name'   => 'Bar Breva',
			'sub'    => '',
			'type'   => 'place',
			'x'      => 1150,
			'y'      => 690,
		),
		array(
			'id'     => 'tremezzo',
			'marker' => '4',
			'name'   => 'Casa Tremezzo',
			'sub'    => 'guest house',
			'type'   => 'place',
			'x'      => 1230,
			'y'      => 455,
		),
		array(
			'id'     => 'waste',
			'marker' => '5',
			'name'   => 'Waste Collection Point',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 660,
			'y'      => 668,
		),
		array(
			'id'     => 'parking',
			'marker' => 'P',
			'name'   => 'Parking',
			'sub'    => 'on Via Castello (south side)',
			'type'   => 'service',
			'x'      => 175,
			'y'      => 820,
		),
		array(
			'id'     => 'garden',
			'marker' => '✿',
			'name'   => 'Courtyard & garden',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 1080,
			'y'      => 480,
		),
		array(
			'id'     => 'entrance',
			'marker' => '→',
			'name'   => 'Main entrance',
			'sub'    => '',
			'type'   => 'service',
			'x'      => 225,
			'y'      => 520,
		),
	);
}

/**
 * Static SVG scenery: grounds, woods, Via Castello + parking, courtyards, garden,
 * the traced building ring and the entrance. viewBox is 1400x1000.
 *
 * Orientation matches the drone photo: the street/entrance are on the LEFT (west),
 * the buildings wrap as an irregular ring around two courtyards, and the planted
 * garden bed sits in the main (upper-right) courtyard. Building groups carry
 * class="estate-map__building" data-poi="{id}" so they highlight with the legend.
 *
 * @return string
 */
function pediment_child_estate_map_scenery() {
	return <<<'SVG'
<defs>
	<linearGradient id="wc-roof" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C67E5C"/><stop offset="1" stop-color="#A9583B"/></linearGradient>
	<linearGradient id="wc-roof2" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#BE7150"/><stop offset="1" stop-color="#9E4F35"/></linearGradient>
	<radialGradient id="wc-grass" cx="50%" cy="45%" r="80%"><stop offset="0" stop-color="#9FBC7A"/><stop offset="1" stop-color="#7E9C5C"/></radialGradient>
	<filter id="wc-soft" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="6" stdDeviation="7" flood-color="#2c1c0e" flood-opacity="0.22"/></filter>
</defs>
<rect width="1400" height="1000" fill="url(#wc-grass)"/>
<ellipse cx="320" cy="880" rx="300" ry="150" fill="#A7C382" opacity=".45"/>
<ellipse cx="1180" cy="850" rx="320" ry="180" fill="#A7C382" opacity=".4"/>
<!-- surrounding woods -->
<g fill="#4f7038">
	<circle cx="110" cy="80" r="82"/><circle cx="300" cy="64" r="92"/><circle cx="500" cy="72" r="82"/><circle cx="720" cy="54" r="96"/><circle cx="960" cy="64" r="90"/><circle cx="1180" cy="74" r="92"/><circle cx="1340" cy="140" r="92"/>
	<circle cx="1360" cy="380" r="82"/><circle cx="1360" cy="600" r="82"/><circle cx="1310" cy="820" r="92"/>
	<circle cx="56" cy="300" r="70"/><circle cx="56" cy="520" r="70"/>
</g>
<g fill="#6b9450">
	<circle cx="110" cy="60" r="54"/><circle cx="300" cy="46" r="60"/><circle cx="720" cy="36" r="62"/><circle cx="960" cy="46" r="58"/><circle cx="1180" cy="56" r="58"/>
</g>
<!-- prominent lone tree west of the house -->
<g transform="translate(175,560)"><circle r="72" fill="#4f7038"/><circle cx="-22" cy="-20" r="46" fill="#6b9450"/></g>
<!-- Via Castello (enters from the left); guest parking on its south side -->
<path d="M-30 790 Q180 748 330 646" fill="none" stroke="#CFC3A2" stroke-width="50" stroke-linecap="round"/>
<path d="M-30 790 Q180 748 330 646" fill="none" stroke="#E7DCBF" stroke-width="36" stroke-linecap="round"/>
<text x="34" y="744" fill="#5B5042" font-family="'Inria Sans', sans-serif" font-size="24" font-style="italic" transform="rotate(-17 34 744)">Via Castello</text>
<g fill="#EAE2CF" stroke="#CBBE9E" stroke-width="3">
	<rect x="74" y="836" width="74" height="48" rx="6" transform="rotate(-17 111 860)"/>
	<rect x="168" y="818" width="74" height="48" rx="6" transform="rotate(-17 205 842)"/>
	<rect x="262" y="798" width="74" height="48" rx="6" transform="rotate(-17 299 822)"/>
</g>
<!-- driveway to the entrance -->
<path d="M330 660 Q300 600 258 556" fill="none" stroke="#E7DCBF" stroke-width="22" stroke-linecap="round"/>
<!-- main courtyard (upper right) + cloister courtyard (centre) -->
<path class="estate-map__courtyard" d="M985 470 Q1080 360 1235 360 Q1345 400 1362 520 Q1352 622 1240 642 Q1082 652 1020 586 Q980 530 985 470 Z" fill="#EFE7D2" stroke="#D9CBAA" stroke-width="3"/>
<rect x="560" y="558" width="330" height="196" rx="14" fill="#E7DFCB" stroke="#D3C6A6" stroke-width="3"/>
<!-- planted garden bed in the main courtyard -->
<g class="estate-map__building" data-poi="garden">
	<circle cx="1080" cy="480" r="60" fill="#6E9A4E"/>
	<g fill="#8CB86B"><circle cx="1058" cy="460" r="8"/><circle cx="1100" cy="468" r="8"/><circle cx="1076" cy="502" r="8"/><circle cx="1110" cy="500" r="8"/><circle cx="1046" cy="494" r="8"/><circle cx="1088" cy="480" r="8"/></g>
</g>
<!-- building ring -->
<g filter="url(#wc-soft)" stroke="#6B3B22" stroke-width="4.5" stroke-linejoin="round">
	<!-- north wing (historic range, unlabeled) -->
	<polygon points="300,432 352,300 902,250 1096,306 1068,414 872,366 336,456" fill="url(#wc-roof)"/>
	<line x1="366" y1="332" x2="1052" y2="364" stroke="#8A4730" stroke-width="3"/>
	<!-- Casa Tremezzo (east wing) -->
	<g class="estate-map__building" data-poi="tremezzo">
		<polygon points="1096,306 1276,378 1380,498 1334,600 1204,604 1122,502 1068,414" fill="url(#wc-roof2)"/>
	</g>
	<!-- Bar Breva (lower-right wing) -->
	<g class="estate-map__building" data-poi="bar">
		<polygon points="1204,604 1334,600 1300,724 1140,794 1030,722 1086,612" fill="url(#wc-roof)"/>
	</g>
	<!-- Co-Working (south wing) -->
	<g class="estate-map__building" data-poi="coworking">
		<polygon points="1030,722 1140,794 952,884 730,888 666,798 786,728" fill="url(#wc-roof2)"/>
		<polygon points="666,798 730,888 520,892 470,808 560,740" fill="url(#wc-roof)"/>
	</g>
	<!-- Casa Galbiga (bottom-left house) -->
	<g class="estate-map__building" data-poi="galbiga">
		<polygon points="250,600 300,440 472,456 498,720 458,888 302,908 236,760" fill="url(#wc-roof2)"/>
		<line x1="362" y1="474" x2="384" y2="874" stroke="#8A4730" stroke-width="3"/>
	</g>
	<!-- central range (unlabeled) -->
	<polygon points="560,540 862,540 898,632 616,660 540,600" fill="url(#wc-roof)"/>
	<!-- Waste Collection Point (small structure off the cloister) -->
	<g class="estate-map__building" data-poi="waste">
		<rect x="604" y="628" width="112" height="86" rx="6" fill="url(#wc-roof2)"/>
	</g>
</g>
<!-- a couple of trees flanking the grounds -->
<g transform="translate(1300,470)"><circle r="30" fill="#4f7038"/><circle cx="-9" cy="-8" r="20" fill="#6b9450"/></g>
<g transform="translate(560,940)"><circle r="40" fill="#4f7038"/><circle cx="-12" cy="-12" r="26" fill="#6b9450"/></g>
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
				<svg class="estate-map__svg" viewBox="0 0 1400 1000" role="img" aria-label="<?php echo esc_attr__( 'Illustrated map of Workation Castle showing the guest houses, co-working space, bar and grounds', 'pediment-child' ); ?>">
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
